<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CompanyUsersRestApi\Api\Storefront\Mapper;

use Generated\Shared\Transfer\CompanyUserTransfer;

interface CompanyUsersResourceMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function mapCompanyUserTransferToResourceData(CompanyUserTransfer $companyUserTransfer): array;
}
