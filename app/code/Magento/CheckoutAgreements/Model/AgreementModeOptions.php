<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

/**
 * Class \Magento\CheckoutAgreements\Model\AgreementModeOptions
 *
 * @since 2.0.0
 */
class AgreementModeOptions
{
    const MODE_AUTO = 0;

    const MODE_MANUAL = 1;

    /**
     * Return list of agreement mode options array.
     *
     * @return array
     * @since 2.0.0
     */
    public function getOptionsArray()
    {
        return [
            self::MODE_AUTO => __('Automatically'),
            self::MODE_MANUAL => __('Manually')
        ];
    }
}
