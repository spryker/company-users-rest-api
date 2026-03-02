<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompanyUsersRestApi;

use Spryker\Glue\CompanyUsersRestApi\Dependency\Client\CompanyUsersRestApiToCompanyUserClientInterface;
use Spryker\Glue\CompanyUsersRestApi\Dependency\Client\CompanyUsersRestApiToCompanyUserStorageClientInterface;
use Spryker\Glue\CompanyUsersRestApi\Processor\CompanyUser\CompanyUserReader;
use Spryker\Glue\CompanyUsersRestApi\Processor\CompanyUser\CompanyUserReaderInterface;
use Spryker\Glue\CompanyUsersRestApi\Processor\CompanyUser\CompanyUserValidator;
use Spryker\Glue\CompanyUsersRestApi\Processor\CompanyUser\CompanyUserValidatorInterface;
use Spryker\Glue\CompanyUsersRestApi\Processor\CompanyUser\Relationship\CompanyUserByQuoteRequestResourceRelationshipExpander;
use Spryker\Glue\CompanyUsersRestApi\Processor\CompanyUser\Relationship\CompanyUserByShareDetailResourceRelationshipExpander;
use Spryker\Glue\CompanyUsersRestApi\Processor\CompanyUser\Relationship\CompanyUserResourceRelationshipExpanderInterface;
use Spryker\Glue\CompanyUsersRestApi\Processor\Customer\CustomerExpander;
use Spryker\Glue\CompanyUsersRestApi\Processor\Customer\CustomerExpanderInterface;
use Spryker\Glue\CompanyUsersRestApi\Processor\Expander\CheckoutRequestExpander;
use Spryker\Glue\CompanyUsersRestApi\Processor\Expander\CheckoutRequestExpanderInterface;
use Spryker\Glue\CompanyUsersRestApi\Processor\Mapper\CompanyUserMapper;
use Spryker\Glue\CompanyUsersRestApi\Processor\Mapper\CompanyUserMapperInterface;
use Spryker\Glue\CompanyUsersRestApi\Processor\RestResponseBuilder\CompanyUserRestResponseBuilder;
use Spryker\Glue\CompanyUsersRestApi\Processor\RestResponseBuilder\CompanyUserRestResponseBuilderInterface;
use Spryker\Glue\Kernel\AbstractFactory;

/**
 * @method \Spryker\Glue\CompanyUsersRestApi\CompanyUsersRestApiConfig getConfig()
 * @method \Spryker\Client\CompanyUsersRestApi\CompanyUsersRestApiClientInterface getClient()
 */
class CompanyUsersRestApiFactory extends AbstractFactory
{
    public function createCompanyUserReader(): CompanyUserReaderInterface
    {
        return new CompanyUserReader(
            $this->getCompanyUserClient(),
            $this->getClient(),
            $this->createCompanyUserRestResponseBuilder(),
            $this->getCompanyUserStorageClient(),
        );
    }

    public function createCompanyUserRestResponseBuilder(): CompanyUserRestResponseBuilderInterface
    {
        return new CompanyUserRestResponseBuilder(
            $this->getResourceBuilder(),
            $this->createCompanyUserMapper(),
        );
    }

    public function createCompanyUserMapper(): CompanyUserMapperInterface
    {
        return new CompanyUserMapper();
    }

    public function createCustomerExpander(): CustomerExpanderInterface
    {
        return new CustomerExpander();
    }

    public function createCompanyUserByShareDetailResourceRelationshipExpander(): CompanyUserResourceRelationshipExpanderInterface
    {
        return new CompanyUserByShareDetailResourceRelationshipExpander(
            $this->createCompanyUserRestResponseBuilder(),
            $this->createCompanyUserMapper(),
        );
    }

    public function createCompanyUserByQuoteRequestResourceRelationshipExpander(): CompanyUserResourceRelationshipExpanderInterface
    {
        return new CompanyUserByQuoteRequestResourceRelationshipExpander(
            $this->createCompanyUserRestResponseBuilder(),
            $this->createCompanyUserMapper(),
        );
    }

    public function createCompanyUserValidator(): CompanyUserValidatorInterface
    {
        return new CompanyUserValidator(
            $this->getConfig(),
        );
    }

    public function createCheckoutRequestExpander(): CheckoutRequestExpanderInterface
    {
        return new CheckoutRequestExpander();
    }

    public function getCompanyUserClient(): CompanyUsersRestApiToCompanyUserClientInterface
    {
        return $this->getProvidedDependency(CompanyUsersRestApiDependencyProvider::CLIENT_COMPANY_USER);
    }

    public function getCompanyUserStorageClient(): CompanyUsersRestApiToCompanyUserStorageClientInterface
    {
        return $this->getProvidedDependency(CompanyUsersRestApiDependencyProvider::CLIENT_COMPANY_USER_STORAGE);
    }
}
