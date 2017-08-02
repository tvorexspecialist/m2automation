<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

/**
 * Class \Magento\Multishipping\Controller\Checkout\BackToShipping
 *
 * @since 2.0.0
 */
class BackToShipping extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_getState()->setActiveStep(State::STEP_SHIPPING);
        $this->_getState()->unsCompleteStep(State::STEP_BILLING);
        $this->_redirect('*/*/shipping');
    }
}
