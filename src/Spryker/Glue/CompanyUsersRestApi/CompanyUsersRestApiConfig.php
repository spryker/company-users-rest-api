<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompanyUsersRestApi;

use Spryker\Glue\Kernel\AbstractBundleConfig;

class CompanyUsersRestApiConfig extends AbstractBundleConfig
{
    /**
     * @api
     *
     * @var string
     */
    public const RESOURCE_COMPANY_USERS = 'company-users';

    /**
     * @api
     *
     * @var string
     */
    public const CONTROLLER_RESOURCE_COMPANY_USERS = 'company-users-resource';

    /**
     * @api
     *
     * @var string
     */
    public const RESOURCE_COMPANY_USERS_GET_ACTION_NAME = 'get';

    /**
     * @api
     *
     * @uses \Spryker\Glue\CompanyBusinessUnitsRestApi\CompanyBusinessUnitsRestApiConfig::RESOURCE_COMPANY_BUSINESS_UNITS
     *
     * @var string
     */
    public const RESOURCE_COMPANY_BUSINESS_UNITS = 'company-business-units';

    /**
     * @api
     *
     * @uses \Spryker\Glue\CompanyRolesRestApi\CompanyRolesRestApiConfig::RESOURCE_COMPANY_ROLES
     *
     * @var string
     */
    public const RESOURCE_COMPANY_ROLES = 'company-roles';

    /**
     * @api
     *
     * @var string
     */
    public const RESPONSE_CODE_REST_USER_IS_NOT_A_COMPANY_USER = '1401';

    /**
     * @api
     *
     * @var string
     */
    public const RESPONSE_DETAIL_REST_USER_IS_NOT_A_COMPANY_USER = 'Rest user is not a company user.';

    /**
     * @api
     *
     * @var string
     */
    public const RESPONSE_CODE_COMPANY_USER_NOT_SELECTED = '1403';

    /**
     * @api
     *
     * @var string
     */
    public const RESPONSE_DETAIL_COMPANY_USER_NOT_SELECTED = 'Current company user is not set. You need to select the current company user with /company-user-access-tokens in order to access the resource collection.';

    /**
     * @api
     *
     * @var string
     */
    public const RESPONSE_CODE_COMPANY_USER_NOT_FOUND = '1404';

    /**
     * @api
     *
     * @var string
     */
    public const RESPONSE_DETAIL_COMPANY_USER_NOT_FOUND = 'Company user not found';

    /**
     * @api
     *
     * @var string
     */
    public const RESPONSE_CODE_COMPANY_USER_HAS_NO_PERMISSION = '1405';

    /**
     * @api
     *
     * @var string
     */
    public const RESPONSE_DETAIL_COMPANY_USER_HAS_NO_PERMISSION = 'Forbidden';

    /**
     * @api
     *
     * @uses \Spryker\Glue\GlueApplication\GlueApplicationConfig::COLLECTION_IDENTIFIER_CURRENT_USER
     *
     * @var string
     */
    public const COLLECTION_IDENTIFIER_CURRENT_USER = 'mine';

    /**
     * @var array<string>
     */
    protected const COMPANY_USER_RESOURCES = [];

    /**
     * Specification:
     * - Returns resources which are accessible only for company users.
     *
     * @api
     *
     * @return array<string>
     */
    public function getCompanyUserResources(): array
    {
        return static::COMPANY_USER_RESOURCES;
    }
}
