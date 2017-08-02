<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter\Select;

/**
 * Class \Magento\Backend\Block\Widget\Grid\Column\Filter\Select\Extended
 *
 * @since 2.0.0
 */
class Extended extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * Get options for filter value
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getOptions()
    {
        $emptyOption = ['value' => null, 'label' => ''];

        $optionGroups = $this->getColumn()->getOptionGroups();
        if ($optionGroups) {
            array_unshift($optionGroups, $emptyOption);
            return $optionGroups;
        }

        $colOptions = $this->getColumn()->getOptions();
        if (!empty($colOptions) && is_array($colOptions)) {
            $options = [$emptyOption];
            foreach ($colOptions as $value => $label) {
                $options[] = ['value' => $value, 'label' => $label];
            }
            return $options;
        }
        return [];
    }
}
