<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\CompanyUsersRestApi;

use Spryker\Client\CompanyUsersRestApi\Dependency\Client\CompanyUsersRestApiToZedRequestClientInterface;
use Spryker\Client\CompanyUsersRestApi\Zed\CompanyUsersRestApiStub;
use Spryker\Client\CompanyUsersRestApi\Zed\CompanyUsersRestApiStubInterface;
use Spryker\Client\Kernel\AbstractFactory;

class CompanyUsersRestApiFactory extends AbstractFactory
{
    public function createCompanyUsersRestApiStub(): CompanyUsersRestApiStubInterface
    {
        return new CompanyUsersRestApiStub($this->getZedRequestClient());
    }

    public function getZedRequestClient(): CompanyUsersRestApiToZedRequestClientInterface
    {
        return $this->getProvidedDependency(CompanyUsersRestApiDependencyProvider::CLIENT_ZED_REQUEST);
    }
}
