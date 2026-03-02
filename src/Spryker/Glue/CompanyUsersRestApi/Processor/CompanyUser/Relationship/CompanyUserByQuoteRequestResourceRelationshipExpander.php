<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompanyUsersRestApi\Processor\CompanyUser\Relationship;

use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;

class CompanyUserByQuoteRequestResourceRelationshipExpander extends AbstractCompanyUserResourceRelationshipExpander
{
    protected function findCompanyUserTransferInPayload(RestResourceInterface $resource): ?CompanyUserTransfer
    {
        /**
         * @var \Generated\Shared\Transfer\QuoteRequestTransfer|null $payload
         */
        $payload = $resource->getPayload();

        if (
            !$payload ||
            !($payload instanceof QuoteRequestTransfer) || /** @phpstan-ignore instanceof.alwaysTrue */
            $payload->getCompanyUser() === null ||
            $payload->getCompanyUser()->getUuid() === null
        ) {
            return null;
        }

        return $payload->getCompanyUser();
    }
}
