<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Hosted Pro link infoblock
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Paypal\Block\Hosted\Pro;

/**
 * Class \Magento\Paypal\Block\Hosted\Pro\Info
 *
 * @since 2.0.0
 */
class Info extends \Magento\Paypal\Block\Payment\Info
{
    /**
     * Don't show CC type
     *
     * @return false
     * @since 2.0.0
     */
    public function getCcTypeName()
    {
        return false;
    }
}
