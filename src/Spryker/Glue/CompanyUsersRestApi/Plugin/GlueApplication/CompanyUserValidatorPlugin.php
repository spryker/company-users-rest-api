<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompanyUsersRestApi\Plugin\GlueApplication;

use Generated\Shared\Transfer\RestErrorCollectionTransfer;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\RestUserValidatorPluginInterface;
use Spryker\Glue\Kernel\AbstractPlugin;

/**
 * @method \Spryker\Glue\CompanyUsersRestApi\CompanyUsersRestApiFactory getFactory()
 */
class CompanyUserValidatorPlugin extends AbstractPlugin implements RestUserValidatorPluginInterface
{
    /**
     * {@inheritDoc}
     * - Checks that rest user is a company user.
     * - Returns null on successful validation and a collection of error messages on validation failure.
     *
     * @api
     *
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Generated\Shared\Transfer\RestErrorCollectionTransfer|null
     */
    public function validate(RestRequestInterface $restRequest): ?RestErrorCollectionTransfer
    {
        return $this->getFactory()->createCompanyUserValidator()->validate($restRequest);
    }
}
