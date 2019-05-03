<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompanyUsersRestApi\Processor\CompanyUser;

use Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\FilterTransfer;
use Spryker\Client\CompanyUsersRestApi\CompanyUsersRestApiClientInterface;
use Spryker\Glue\CompanyUsersRestApi\CompanyUsersRestApiConfig;
use Spryker\Glue\CompanyUsersRestApi\Dependency\Client\CompanyUsersRestApiToCompanyUserClientInterface;
use Spryker\Glue\CompanyUsersRestApi\Dependency\Client\CompanyUsersRestApiToCompanyUserStorageClientInterface;
use Spryker\Glue\CompanyUsersRestApi\Processor\RestResponseBuilder\CompanyUserRestResponseBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

class CompanyUserReader implements CompanyUserReaderInterface
{
    protected const MAPPING_TYPE_UUID = 'uuid';

    /**
     * @var \Spryker\Glue\CompanyUsersRestApi\Dependency\Client\CompanyUsersRestApiToCompanyUserClientInterface
     */
    protected $companyUserClient;

    /**
     * @var \Spryker\Client\CompanyUsersRestApi\CompanyUsersRestApiClientInterface
     */
    protected $companyUsersRestApiClient;

    /**
     * @var \Spryker\Glue\CompanyUsersRestApi\Processor\RestResponseBuilder\CompanyUserRestResponseBuilderInterface
     */
    protected $companyUserRestResponse;

    /**
     * @var \Spryker\Glue\CompanyUsersRestApi\Dependency\Client\CompanyUsersRestApiToCompanyUserStorageClientInterface
     */
    protected $companyUserStorageClient;

    /**
     * @param \Spryker\Glue\CompanyUsersRestApi\Dependency\Client\CompanyUsersRestApiToCompanyUserClientInterface $companyUserClient
     * @param \Spryker\Client\CompanyUsersRestApi\CompanyUsersRestApiClientInterface $companyUsersRestApiClient
     * @param \Spryker\Glue\CompanyUsersRestApi\Processor\RestResponseBuilder\CompanyUserRestResponseBuilderInterface $companyUserRestResponse
     * @param \Spryker\Glue\CompanyUsersRestApi\Dependency\Client\CompanyUsersRestApiToCompanyUserStorageClientInterface $companyUserStorageClient
     */
    public function __construct(
        CompanyUsersRestApiToCompanyUserClientInterface $companyUserClient,
        CompanyUsersRestApiClientInterface $companyUsersRestApiClient,
        CompanyUserRestResponseBuilderInterface $companyUserRestResponse,
        CompanyUsersRestApiToCompanyUserStorageClientInterface $companyUserStorageClient
    ) {
        $this->companyUserClient = $companyUserClient;
        $this->companyUsersRestApiClient = $companyUsersRestApiClient;
        $this->companyUserRestResponse = $companyUserRestResponse;
        $this->companyUserStorageClient = $companyUserStorageClient;
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function getCompanyUsersByCustomerReference(RestRequestInterface $restRequest): RestResponseInterface
    {
        $customerTransfer = (new CustomerTransfer())
            ->setCustomerReference($restRequest->getRestUser()->getNaturalIdentifier());
        $companyUserCollectionTransfer = $this->companyUserClient
            ->getActiveCompanyUsersByCustomerReference($customerTransfer);

        return $this->companyUserRestResponse->buildCompanyUserCollectionResponse($companyUserCollectionTransfer);
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function getCompanyUserByResourceId(RestRequestInterface $restRequest): RestResponseInterface
    {
        $idResource = $restRequest->getResource()->getId();

        if ($idResource === CompanyUsersRestApiConfig::CURRENT_USER_COLLECTION_IDENTIFIER) {
            return $this->getCompanyUsersByCustomerReference($restRequest);
        }

        return $this->getCompanyUser($idResource, $restRequest);
    }

    /**
     * @param string $companyUserUuid
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    protected function getCompanyUser(string $companyUserUuid, RestRequestInterface $restRequest): RestResponseInterface
    {
        $companyUserStorageTransfer = $this->companyUserStorageClient
            ->findCompanyUserByMapping(static::MAPPING_TYPE_UUID, $companyUserUuid);
        if (!$companyUserStorageTransfer) {
            return $this->companyUserRestResponse->buildNotFoundErrorResponse();
        }

        $companyUserTransfer = (new CompanyUserTransfer())
            ->setUuid($companyUserUuid)
            ->setIdCompanyUser($companyUserStorageTransfer->getIdCompanyUser());
        $companyUserTransfer = $this->companyUserClient->getCompanyUserById($companyUserTransfer);

        if ($companyUserTransfer->getCompany()->getIdCompany() !== $restRequest->getRestUser()->getIdCompany()) {
            return $this->companyUserRestResponse->buildForbiddenErrorResponse();
        }

        return $this->companyUserRestResponse->buildCompanyUserResponse($companyUserTransfer);
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function getCompanyUserCollection(RestRequestInterface $restRequest): RestResponseInterface
    {
        $idCompany = $restRequest->getRestUser()->getIdCompany();
        if (!$idCompany) {
            return $this->companyUserRestResponse->buildForbiddenErrorResponse();
        }

        $companyUserCriteriaFilterTransfer = (new CompanyUserCriteriaFilterTransfer())
            ->setIdCompany($idCompany)
            ->setFilter($this->createFilterTransfer($restRequest));

        $companyUserCriteriaFilterTransfer = $this->applyFilters($restRequest, $companyUserCriteriaFilterTransfer);

        $companyUserCollectionTransfer = $this->companyUsersRestApiClient->getCompanyUserCollection(
            $companyUserCriteriaFilterTransfer->setFilter($this->createFilterTransfer($restRequest))
        );

        return $this->companyUserRestResponse->buildCompanyUserCollectionResponse(
            $companyUserCollectionTransfer,
            $companyUserCollectionTransfer->getTotal(),
            $companyUserCollectionTransfer->getFilter()->getLimit()
        );
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     * @param \Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer
     *
     * @return \Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer
     */
    protected function applyFilters(
        RestRequestInterface $restRequest,
        CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer
    ): CompanyUserCriteriaFilterTransfer {
        if ($restRequest->hasFilters(CompanyUsersRestApiConfig::RESOURCE_COMPANY_USERS)) {
            $companyUserCriteriaFilterTransfer = $this->applyFilterByCompanyUsers(
                $restRequest,
                $companyUserCriteriaFilterTransfer
            );
        }

        if ($restRequest->hasFilters(CompanyUsersRestApiConfig::RESOURCE_COMPANY_BUSINESS_UNITS)) {
            $companyUserCriteriaFilterTransfer = $this->applyFilterByCompanyBusinessUnits(
                $restRequest,
                $companyUserCriteriaFilterTransfer
            );
        }

        return $companyUserCriteriaFilterTransfer;
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     * @param \Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer
     *
     * @return \Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer
     */
    protected function applyFilterByCompanyUsers(
        RestRequestInterface $restRequest,
        CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer
    ): CompanyUserCriteriaFilterTransfer {
        $filterCompanyUsers = $restRequest->getFiltersByResource(CompanyUsersRestApiConfig::RESOURCE_COMPANY_USERS);
        foreach ($filterCompanyUsers as $filterCompanyUser) {
            $companyUserUuid = $filterCompanyUser->getValue();
            $companyUserStorageTransfer = $this->companyUserStorageClient
                ->findCompanyUserByMapping(static::MAPPING_TYPE_UUID, $companyUserUuid);
            if ($companyUserStorageTransfer) {
                $companyUserCriteriaFilterTransfer
                    ->addCompanyUserIds($companyUserStorageTransfer->getIdCompanyUser());
            }
        }

        return $companyUserCriteriaFilterTransfer;
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     * @param \Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer
     *
     * @return \Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer
     */
    protected function applyFilterByCompanyBusinessUnits(
        RestRequestInterface $restRequest,
        CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer
    ): CompanyUserCriteriaFilterTransfer {
        $filterCompanyBusinessUnits = $restRequest->getFiltersByResource(CompanyUsersRestApiConfig::RESOURCE_COMPANY_BUSINESS_UNITS);
        foreach ($filterCompanyBusinessUnits as $filterCompanyBusinessUnit) {
            $companyBusinessUnitUuid = $filterCompanyBusinessUnit->getValue();
            $companyUserStorageTransfer = $this->companyUserStorageClient
                ->findCompanyUserByMapping(static::MAPPING_TYPE_UUID, $companyBusinessUnitUuid);
            if ($companyUserStorageTransfer) {
                $companyUserCriteriaFilterTransfer
                    ->addCompanyBusinessUnitUuids($companyUserStorageTransfer->getUuid());
            }
        }

        return $companyUserCriteriaFilterTransfer;
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Generated\Shared\Transfer\FilterTransfer
     */
    protected function createFilterTransfer(RestRequestInterface $restRequest): FilterTransfer
    {
        return (new FilterTransfer())
            ->setOffset($restRequest->getPage() ? $restRequest->getPage()->getOffset() : 0)
            ->setLimit($restRequest->getPage() ? $restRequest->getPage()->getLimit() : 0);
    }
}
