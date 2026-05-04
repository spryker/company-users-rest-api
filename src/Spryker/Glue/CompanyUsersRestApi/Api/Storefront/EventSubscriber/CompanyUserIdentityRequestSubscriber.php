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
 */
#[ApiType(types: ['storefront'])]
class CompanyUserIdentityRequestSubscriber implements EventSubscriberInterface
{
    protected const string ATTRIBUTE_CUSTOMER_TRANSFER = 'CustomerTransfer';

    protected const string KEY_COMPANY_USER_ID = 'id_company_user';

    protected const int PRIORITY_AFTER_CUSTOMER = 5;

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

        $customerTransfer->setCompanyUserTransfer(
            (new CompanyUserTransfer())->setUuid((string)$claims[static::KEY_COMPANY_USER_ID]),
        );
    }
}
