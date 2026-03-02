<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompanyUsersRestApi\Processor\Expander;

use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\RestCheckoutRequestAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

class CheckoutRequestExpander implements CheckoutRequestExpanderInterface
{
    public function expand(
        RestRequestInterface $restRequest,
        RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer
    ): RestCheckoutRequestAttributesTransfer {
        if (!$this->isRequestExpandable($restRequest, $restCheckoutRequestAttributesTransfer)) {
            return $restCheckoutRequestAttributesTransfer;
        }

        $restCustomerTransfer = $restCheckoutRequestAttributesTransfer->getCustomer();
        $restCustomerTransfer->setIdCompanyUser($restRequest->getRestUser()->getIdCompanyUser())
            ->setIdCompany($restRequest->getRestUser()->getIdCompany())
            ->setIdCompanyBusinessUnit($restRequest->getRestUser()->getIdCompanyBusinessUnit())
            ->setCompanyUserTransfer((new CompanyUserTransfer())->setIdCompanyUser($restRequest->getRestUser()->getIdCompanyUser()));

        return $restCheckoutRequestAttributesTransfer->setCustomer($restCustomerTransfer);
    }

    protected function isRequestExpandable(
        RestRequestInterface $restRequest,
        RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer
    ): bool {
        return $restRequest->getRestUser()
            && $restRequest->getRestUser()->getIdCompanyUser()
            && $restCheckoutRequestAttributesTransfer->getCustomer();
    }
}
