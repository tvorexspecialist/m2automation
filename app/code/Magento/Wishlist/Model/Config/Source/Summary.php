<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model\Config\Source;

/**
 * Class \Magento\Wishlist\Model\Config\Source\Summary
 *
 */
class Summary implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Display number of items in wish list')],
            ['value' => 1, 'label' => __('Display item quantities')]
        ];
    }
}
