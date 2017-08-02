<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Source\Option\Selection\Price;

use Magento\Bundle\Api\Data\LinkInterface;

/**
 * Extended Attributes Source Model
 *
 * @api
 * @since 2.0.0
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => LinkInterface::PRICE_TYPE_FIXED, 'label' => __('Fixed')],
            ['value' => LinkInterface::PRICE_TYPE_PERCENT, 'label' => __('Percent')]
        ];
    }
}
