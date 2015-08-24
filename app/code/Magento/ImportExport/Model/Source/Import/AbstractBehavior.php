<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Source\Import;

/**
 * Source import behavior model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractBehavior implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get array of possible values
     *
     * @abstract
     * @return array
     */
    abstract public function toArray();

    /**
     * Prepare and return array of option values
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [['label' => __('-- Please Select --'), 'value' => '']];
        $options = $this->toArray();
        if (is_array($options) && count($options) > 0) {
            foreach ($options as $value => $label) {
                $optionArray[] = ['label' => $label, 'value' => $value];
            }
        }
        return $optionArray;
    }

    /**
     * Get current behaviour group code
     *
     * @abstract
     * @return string
     */
    abstract public function getCode();

    /**
     * Get array of notes for possible values
     *
     * @return array
     */
    public function getNotes()
    {
        return [];
    }
}
