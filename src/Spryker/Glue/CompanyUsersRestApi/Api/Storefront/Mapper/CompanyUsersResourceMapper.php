<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Mapper;

use Generated\Shared\Transfer\CompanyUserTransfer;

class CompanyUsersResourceMapper implements CompanyUsersResourceMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function mapCompanyUserTransferToResourceData(CompanyUserTransfer $companyUserTransfer): array
    {
        return [
            'uuid' => $companyUserTransfer->getUuid(),
            'isActive' => $companyUserTransfer->getIsActive(),
            'isDefault' => $companyUserTransfer->getIsDefault(),
            'idCompany' => $companyUserTransfer->getFkCompany(),
            'idCompanyBusinessUnit' => $companyUserTransfer->getFkCompanyBusinessUnit(),
            'idCompanyUser' => $companyUserTransfer->getIdCompanyUser(),
            'customerReference' => $companyUserTransfer->getCustomer()?->getCustomerReference(),
            'customerTransferData' => $companyUserTransfer->getCustomer() !== null ? (object)$companyUserTransfer->getCustomer()->toArray() : null,
        ];
    }
}
