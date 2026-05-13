<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Mapper;

use Generated\Shared\Transfer\CompanyUserTransfer;

/**
 * Flattens {@see CompanyUserTransfer} into the storefront resource attribute array.
 * Matches the legacy `RestCompanyUserAttributes` surface — only `isActive` and `isDefault`.
 * The `uuid` becomes the JSON:API `data.id`.
 */
class CompanyUserResourceMapper
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
        ];
    }
}
