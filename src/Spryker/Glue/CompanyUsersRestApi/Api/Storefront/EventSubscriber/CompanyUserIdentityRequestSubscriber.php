<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompanyUsersRestApi\Api\Storefront\EventSubscriber;

use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\Attribute\ApiType;
use Spryker\ApiPlatform\EventSubscriber\IdentityRequestSubscriber;
use Spryker\Client\CompanyUserStorage\CompanyUserStorageClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Augments the {@see CustomerTransfer} on the request (set by
 * {@see \Spryker\Glue\CustomersRestApi\Api\Storefront\EventSubscriber\CustomerIdentityRequestSubscriber})
 * with a {@see CompanyUserTransfer} when the OAuth claims include an `id_company_user` value.
 *
 * Knowledge of company-user-specific claim keys lives here, not in the generic API Platform
 * layer or in CustomersRestApi. Runs at lower priority than the customer subscriber so the
 * parent CustomerTransfer is already available on the request.
 *
 * After building the transfer from the claim UUID the subscriber enriches it with `fkCompany`
 * by looking up the company user in storage. A missing storage record is non-fatal — the
 * transfer is attached with only the uuid, matching the previous behaviour.
 */
#[ApiType(types: ['storefront'])]
class CompanyUserIdentityRequestSubscriber implements EventSubscriberInterface
{
    protected const string ATTRIBUTE_CUSTOMER_TRANSFER = 'CustomerTransfer';

    protected const string KEY_COMPANY_USER_ID = 'id_company_user';

    protected const string MAPPING_TYPE_UUID = 'uuid';

    protected const int PRIORITY_AFTER_CUSTOMER = 5;

    public function __construct(
        protected CompanyUserStorageClientInterface $companyUserStorageClient,
    ) {
    }

    /**
     * @return array<string, array{string, int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', static::PRIORITY_AFTER_CUSTOMER],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $claims = $request->attributes->get(IdentityRequestSubscriber::ATTRIBUTE_OAUTH_IDENTITY_CLAIMS);
        $customerTransfer = $request->attributes->get(static::ATTRIBUTE_CUSTOMER_TRANSFER);

        if (
            !is_array($claims)
            || !$customerTransfer instanceof CustomerTransfer
            || !isset($claims[static::KEY_COMPANY_USER_ID])
        ) {
            return;
        }

        $companyUserUuid = (string)$claims[static::KEY_COMPANY_USER_ID];
        $companyUserTransfer = (new CompanyUserTransfer())->setUuid($companyUserUuid);

        // The OAuth `id_company_user` claim carries the company user UUID. Resolve it to the
        // integer `idCompanyUser` so downstream Zed plugins (e.g. SharedCart `DeactivateSharedQuotesBeforeQuoteSavePlugin`,
        // QuotePermissionChecker for shared cart access) that expect `int` get a usable value.
        $companyUserStorageTransfer = $this->companyUserStorageClient
            ->findCompanyUserByMapping(static::MAPPING_TYPE_UUID, $companyUserUuid);

        if ($companyUserStorageTransfer !== null) {
            $companyUserTransfer
                ->setIdCompanyUser($companyUserStorageTransfer->getIdCompanyUser())
                ->setFkCompany($companyUserStorageTransfer->getIdCompany())
                ->setFkCompanyBusinessUnit($companyUserStorageTransfer->getIdCompanyBusinessUnit());
        }

        $customerTransfer->setCompanyUserTransfer($companyUserTransfer);
    }
}
