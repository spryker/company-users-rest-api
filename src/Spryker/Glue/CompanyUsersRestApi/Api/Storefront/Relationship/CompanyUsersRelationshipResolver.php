<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\CompanyUsersStorefrontResource;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Spryker\ApiPlatform\Relationship\PerItemRelationshipResolverInterface;
use Spryker\Client\CompanyUser\CompanyUserClientInterface;
use Spryker\Client\CompanyUserStorage\CompanyUserStorageClientInterface;
use Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Mapper\CompanyUsersResourceMapperInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;

class CompanyUsersRelationshipResolver implements PerItemRelationshipResolverInterface
{
    protected const string MAPPING_TYPE_UUID = 'uuid';

    public function __construct(
        protected CompanyUserStorageClientInterface $companyUserStorageClient,
        protected CompanyUserClientInterface $companyUserClient,
        protected CompanyUsersResourceMapperInterface $companyUsersResourceMapper,
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @param array<object> $parentResources
     * @param array<string, mixed> $context
     *
     * @return array<object>
     */
    public function resolve(array $parentResources, array $context): array
    {
        $allResources = [];

        foreach ($this->resolvePerItem($parentResources, $context) as $resources) {
            $allResources = array_merge($allResources, $resources);
        }

        return $allResources;
    }

    /**
     * @param array<object> $parentResources
     * @param array<string, mixed> $context
     *
     * @return array<string, array<object>>
     */
    public function resolvePerItem(array $parentResources, array $context): array
    {
        $result = [];

        foreach ($parentResources as $parentResource) {
            $companyUserUuid = $parentResource->companyUserUuid ?? null;

            if ($companyUserUuid === null) {
                continue;
            }

            $companyUserTransfer = $this->findCompanyUserByUuid($companyUserUuid);

            $result[$companyUserUuid] = $companyUserTransfer !== null
                ? [$this->denormalizeToResource($companyUserTransfer)]
                : [];
        }

        return $result;
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
