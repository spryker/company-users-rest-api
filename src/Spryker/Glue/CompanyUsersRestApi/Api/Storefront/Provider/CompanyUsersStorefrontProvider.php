<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\CompanyUsersStorefrontResource;
use Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\FilterTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\CompanyUser\CompanyUserClientInterface;
use Spryker\Client\CompanyUsersRestApi\CompanyUsersRestApiClientInterface;
use Spryker\Client\CompanyUserStorage\CompanyUserStorageClientInterface;
use Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Exception\CompanyUsersExceptionFactory;
use Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Mapper\CompanyUsersResourceMapperInterface;
use Spryker\Glue\Kernel\PermissionAwareTrait;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CompanyUsersStorefrontProvider extends AbstractStorefrontProvider
{
    use PermissionAwareTrait;

    protected const string KEY_UUID = 'uuid';

    protected const string MAPPING_TYPE_UUID = 'uuid';

    protected const string OPERATION_NAME_GET_COMPANY_USERS_MINE = 'getCompanyUsersMine';

    protected const string FILTER_KEY = 'filter';

    protected const string FILTER_KEY_COMPANY_BUSINESS_UNITS = 'company-business-units';

    protected const string FILTER_KEY_COMPANY_ROLES = 'company-roles';

    protected const string PERMISSION_SEE_COMPANY_USERS = 'SeeCompanyUsersPermissionPlugin';

    // BC: legacy applied no default limit — 0 means "return all" in Propel queries.
    protected const int DEFAULT_COLLECTION_LIMIT = 0;

    public function __construct(
        protected CompanyUserClientInterface $companyUserClient,
        protected CompanyUsersRestApiClientInterface $companyUsersRestApiClient,
        protected CompanyUserStorageClientInterface $companyUserStorageClient,
        protected CompanyUsersExceptionFactory $companyUsersExceptionFactory,
        protected CompanyUsersResourceMapperInterface $companyUsersResourceMapper,
        protected SerializerServiceInterface $serializer,
    ) {
    }

    protected function provideItem(): ?object
    {
        if (!$this->hasCustomer()) {
            throw new AccessDeniedException();
        }

        return $this->provideCompanyUserByUuid((string)$this->getUriVariable(static::KEY_UUID));
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return array<\Generated\Api\Storefront\CompanyUsersStorefrontResource>
     */
    protected function provideCollection(): array
    {
        if (!$this->hasCustomer()) {
            throw new AccessDeniedException();
        }

        if ($this->getOperation()->getName() === static::OPERATION_NAME_GET_COMPANY_USERS_MINE) {
            return $this->provideMyCompanyUsers();
        }

        return $this->provideCompanyUserCollection();
    }

    /**
     * @return array<\Generated\Api\Storefront\CompanyUsersStorefrontResource>
     */
    protected function provideMyCompanyUsers(): array
    {
        $companyUserCollectionTransfer = $this->companyUserClient->getActiveCompanyUsersByCustomerReference(
            (new CustomerTransfer())->setCustomerReference($this->getCustomerReference()),
        );

        $resources = [];

        foreach ($companyUserCollectionTransfer->getCompanyUsers() as $companyUserTransfer) {
            $resources[] = $this->denormalizeToResource($companyUserTransfer);
        }

        return $resources;
    }

    protected function provideCompanyUserByUuid(string $uuid): CompanyUsersStorefrontResource
    {
        $idCompany = $this->getCurrentUserCompanyId();
        $this->assertCanSeeCompanyUsers();

        $companyUserTransfer = $this->findCompanyUserByUuid($uuid);

        if ($companyUserTransfer === null || $companyUserTransfer->getCompany() === null) {
            throw $this->companyUsersExceptionFactory->createCompanyUserNotFoundException();
        }

        if ($companyUserTransfer->getCompany()->getIdCompany() !== $idCompany) {
            throw $this->companyUsersExceptionFactory->createCompanyUserNotSelectedException();
        }

        return $this->denormalizeToResource($companyUserTransfer);
    }

    /**
     * @return array<\Generated\Api\Storefront\CompanyUsersStorefrontResource>
     */
    protected function provideCompanyUserCollection(): array
    {
        $idCompany = $this->getCurrentUserCompanyId();
        $this->assertCanSeeCompanyUsers();

        $companyUserCriteriaFilterTransfer = (new CompanyUserCriteriaFilterTransfer())
            ->setIdCompany($idCompany)
            ->setFilter(
                (new FilterTransfer())
                    ->setLimit($this->getPaginationParameter(static::QUERY_PARAMETER_LIMIT) ?? static::DEFAULT_COLLECTION_LIMIT)
                    ->setOffset($this->getPaginationOffset()),
            );

        $companyUserCriteriaFilterTransfer = $this->applyFilters($companyUserCriteriaFilterTransfer);

        $companyUserCollectionTransfer = $this->companyUsersRestApiClient->getCompanyUserCollection($companyUserCriteriaFilterTransfer);

        $resources = [];

        foreach ($companyUserCollectionTransfer->getCompanyUsers() as $companyUserTransfer) {
            $resources[] = $this->denormalizeToResource($companyUserTransfer);
        }

        return $resources;
    }

    protected function applyFilters(
        CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer
    ): CompanyUserCriteriaFilterTransfer {
        $filters = $this->getRequest()->query->all(static::FILTER_KEY);

        foreach ($this->extractFilterValues($filters, static::FILTER_KEY_COMPANY_BUSINESS_UNITS) as $uuid) {
            $companyUserCriteriaFilterTransfer->addCompanyBusinessUnitUuids($uuid);
        }

        foreach ($this->extractFilterValues($filters, static::FILTER_KEY_COMPANY_ROLES) as $uuid) {
            $companyUserCriteriaFilterTransfer->addCompanyRolesUuids($uuid);
        }

        return $companyUserCriteriaFilterTransfer;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return list<string>
     */
    protected function extractFilterValues(array $filters, string $key): array
    {
        $value = $filters[$key] ?? null;

        if (is_string($value) && $value !== '') {
            return [$value];
        }

        if (is_array($value)) {
            return array_map('strval', array_values($value));
        }

        return [];
    }

    protected function getCurrentUserCompanyId(): int
    {
        $idCompany = $this->getCustomer()->getCompanyUserTransfer()?->getFkCompany();

        if ($idCompany === null) {
            throw $this->companyUsersExceptionFactory->createCompanyUserNotSelectedException();
        }

        return $idCompany;
    }

    protected function assertCanSeeCompanyUsers(): void
    {
        if ($this->can(static::PERMISSION_SEE_COMPANY_USERS)) {
            return;
        }

        throw $this->companyUsersExceptionFactory->createCompanyUserHasNoPermissionException();
    }

    protected function findCompanyUserByUuid(string $uuid): ?CompanyUserTransfer
    {
        $companyUserStorageTransfer = $this->companyUserStorageClient->findCompanyUserByMapping(
            static::MAPPING_TYPE_UUID,
            $uuid,
        );

        if ($companyUserStorageTransfer === null) {
            return null;
        }

        return $this->companyUserClient->getCompanyUserById(
            (new CompanyUserTransfer())->setIdCompanyUser($companyUserStorageTransfer->getIdCompanyUser()),
        );
    }

    protected function denormalizeToResource(CompanyUserTransfer $companyUserTransfer): CompanyUsersStorefrontResource
    {
        return $this->serializer->denormalize(
            $this->companyUsersResourceMapper->mapCompanyUserTransferToResourceData($companyUserTransfer),
            CompanyUsersStorefrontResource::class,
        );
    }
}
