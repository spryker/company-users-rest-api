<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\CompanyUsersRestApi\CompanyUsersRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class CompanyUsersExceptionFactory
{
    /**
     * Mirrors the legacy `CompanyUserRestResponseBuilder::createCompanyUserNotSelectedErrorResponse`
     * — emitted when the request hits `/company-users/` (trailing-slash, empty `{uuid}`) without
     * an active company user session. BC: legacy emits 403 + 1403 here, not 404.
     */
    public function createCompanyUserNotSelectedException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_FORBIDDEN,
            CompanyUsersRestApiConfig::RESPONSE_CODE_COMPANY_USER_NOT_SELECTED,
            CompanyUsersRestApiConfig::RESPONSE_DETAIL_COMPANY_USER_NOT_SELECTED,
        );
    }

    public function createCompanyUserNotFoundException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            CompanyUsersRestApiConfig::RESPONSE_CODE_COMPANY_USER_NOT_FOUND,
            CompanyUsersRestApiConfig::RESPONSE_DETAIL_COMPANY_USER_NOT_FOUND,
        );
    }

    public function createCompanyUserHasNoPermissionException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_FORBIDDEN,
            CompanyUsersRestApiConfig::RESPONSE_CODE_COMPANY_USER_HAS_NO_PERMISSION,
            CompanyUsersRestApiConfig::RESPONSE_DETAIL_COMPANY_USER_HAS_NO_PERMISSION,
        );
    }
}
