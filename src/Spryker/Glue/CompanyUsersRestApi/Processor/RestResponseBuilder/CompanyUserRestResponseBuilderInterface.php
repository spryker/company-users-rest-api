<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompanyUsersRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\CompanyUserCollectionTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\RestCompanyUserAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface CompanyUserRestResponseBuilderInterface
{
    public function createCompanyUserResponse(
        CompanyUserTransfer $companyUserTransfer
    ): RestResponseInterface;

    public function createCompanyUserCollectionResponse(
        CompanyUserCollectionTransfer $companyUserCollectionTransfer,
        int $totalItems = 0,
        int $limit = 0
    ): RestResponseInterface;

    public function createCompanyUsersRestResource(
        string $companyUserUuid,
        RestCompanyUserAttributesTransfer $restCompanyUserAttributesTransfer,
        CompanyUserTransfer $companyUserTransfer
    ): RestResourceInterface;

    public function createCompanyUserNotSelectedErrorResponse(): RestResponseInterface;

    public function createCompanyUserNotFoundErrorResponse(): RestResponseInterface;

    public function createCompanyUserHasNoPermissionErrorResponse(): RestResponseInterface;
}
