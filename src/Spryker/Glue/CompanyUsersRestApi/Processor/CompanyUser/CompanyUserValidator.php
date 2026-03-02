<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompanyUsersRestApi\Processor\CompanyUser;

use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\Glue\CompanyUsersRestApi\CompanyUsersRestApiConfig;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class CompanyUserValidator implements CompanyUserValidatorInterface
{
    /**
     * @var \Spryker\Glue\CompanyUsersRestApi\CompanyUsersRestApiConfig
     */
    protected $config;

    public function __construct(CompanyUsersRestApiConfig $config)
    {
        $this->config = $config;
    }

    public function validate(RestRequestInterface $restRequest): ?RestErrorMessageTransfer
    {
        if (!$this->isCompanyUserResource($restRequest)) {
            return null;
        }

        if ($this->isCompanyUser($restRequest)) {
            return null;
        }

        return (new RestErrorMessageTransfer())
            ->setDetail(CompanyUsersRestApiConfig::RESPONSE_DETAIL_REST_USER_IS_NOT_A_COMPANY_USER)
            ->setCode(CompanyUsersRestApiConfig::RESPONSE_CODE_REST_USER_IS_NOT_A_COMPANY_USER)
            ->setStatus(Response::HTTP_BAD_REQUEST);
    }

    protected function isCompanyUserResource(RestRequestInterface $restRequest): bool
    {
        return in_array(
            $restRequest->getResource()->getType(),
            $this->config->getCompanyUserResources(),
            true,
        );
    }

    protected function isCompanyUser(RestRequestInterface $restRequest): bool
    {
        $restUserTransfer = $restRequest->getRestUser();
        if (!$restUserTransfer) {
            return false;
        }

        return $restUserTransfer->getIdCompanyUser() !== null && $restUserTransfer->getIdCompany() !== null;
    }
}
