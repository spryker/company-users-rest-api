<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\CompanyUsersStorefrontResource;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\CompanyUser\CompanyUserClientInterface;
use Spryker\Client\CompanyUserStorage\CompanyUserStorageClientInterface;
use Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Exception\CompanyUsersExceptionFactory;
use Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Mapper\CompanyUserResourceMapper;

/**
 * Partial API Platform migration: only `GET /company-users/{uuid}` is served from here.
 * Mutating operations and the collection endpoint remain on the legacy Glue REST stack.
 *
 * Lookup flow mirrors legacy {@see \Spryker\Glue\CompanyUsersRestApi\Processor\CompanyUser\CompanyUserReader::getCompanyUser}:
 *
 *   1. resolve `uuid` → `idCompanyUser` via `CompanyUserStorageClient::findCompanyUserByMapping`
 *   2. hydrate full `CompanyUserTransfer` (including `company`) via `CompanyUserClient::getCompanyUserById`
 *   3. enforce ownership: the resolved company must equal the authenticated customer's company
 *
 * Both failure paths collapse to 404 (legacy 1404), avoiding existence-leak via 403.
 */
class CompanyUsersStorefrontProvider extends AbstractStorefrontProvider
{
    protected const string MAPPING_TYPE_UUID = 'uuid';

    public function __construct(
        protected CompanyUserStorageClientInterface $companyUserStorageClient,
        protected CompanyUserClientInterface $companyUserClient,
        protected CompanyUserResourceMapper $companyUserResourceMapper,
        protected CompanyUsersExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function provideItem(): ?object
    {
        $uuid = $this->getUriVariables()['uuid'] ?? null;

        // BC: `/company-users/` (trailing slash, empty `{uuid}`) is the legacy collection shorthand
        // that returned `403 + 1403 "Current company user is not set..."` for a non-company-user
        // session. The Symfony inline-regex `<.+>` on the route is not enforced by API Platform's
        // routing, so we replay that response here.
        if ($uuid === null || $uuid === '') {
            throw $this->exceptionFactory->createCompanyUserNotSelectedException();
        }

        $companyUserStorageTransfer = $this->companyUserStorageClient
            ->findCompanyUserByMapping(static::MAPPING_TYPE_UUID, $uuid);

        if ($companyUserStorageTransfer === null) {
            throw $this->exceptionFactory->createCompanyUserNotFoundException();
        }

        $companyUserTransfer = $this->companyUserClient->getCompanyUserById(
            (new CompanyUserTransfer())->setIdCompanyUser($companyUserStorageTransfer->getIdCompanyUser()),
        );

        if (!$this->isAuthenticatedCustomerInSameCompany($companyUserTransfer)) {
            throw $this->exceptionFactory->createCompanyUserNotFoundException();
        }

        return CompanyUsersStorefrontResource::fromArray(
            $this->companyUserResourceMapper->mapCompanyUserTransferToResourceData($companyUserTransfer),
        );
    }

    /**
     * Ownership check — the resolved company user must be a peer of the authenticated company
     * user (same `idCompany`). A logged-in customer without a selected company user is treated
     * the same as an outsider → 404.
     */
    protected function isAuthenticatedCustomerInSameCompany(CompanyUserTransfer $companyUserTransfer): bool
    {
        if (!$this->hasCustomer()) {
            return false;
        }

        $authenticatedCompanyUserTransfer = $this->getCustomer()->getCompanyUserTransfer();

        if ($authenticatedCompanyUserTransfer === null) {
            return false;
        }

        $authenticatedIdCompany = $authenticatedCompanyUserTransfer->getFkCompany();
        $resolvedIdCompany = $companyUserTransfer->getCompany()?->getIdCompany();

        return $authenticatedIdCompany !== null && $authenticatedIdCompany === $resolvedIdCompany;
    }
}
