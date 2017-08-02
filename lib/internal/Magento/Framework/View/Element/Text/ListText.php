<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Text;

use Magento\Framework\View\Element\Text;

/**
 * Class ListText
 *
 * @api
 * @since 2.0.0
 */
class ListText extends \Magento\Framework\View\Element\Text
{
    /**
     * Render html output
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $this->setText('');

        $layout = $this->getLayout();
        foreach ($this->getChildNames() as $child) {
            $this->addText($layout->renderElement($child, false));
        }

        return parent::_toHtml();
    }
}
