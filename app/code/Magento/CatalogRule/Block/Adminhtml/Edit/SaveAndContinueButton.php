<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class \Magento\CatalogRule\Block\Adminhtml\Edit\SaveAndContinueButton
 *
 * @since 2.1.0
 */
class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @codeCoverageIgnore
     * @since 2.1.0
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->canRender('save_and_continue_edit')) {
            $data = [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'on_click' => '',
                'sort_order' => 90,
            ];
        }
        return $data;
    }
}
