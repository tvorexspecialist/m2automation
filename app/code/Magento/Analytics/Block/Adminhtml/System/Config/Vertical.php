<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

/**
 * Provides select with industry information
 */
class Vertical extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<div class="config-vertical-title">' . $element->getHint() . '</div>';
        $html .= '<div class="config-vertical-comment">' . $element->getComment() . '</div>';
        return $this->decorateRowHtml($element, $html);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param string $html
     * @return string
     */
    private function decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {
        $rowHtml = sprintf('<tr><td colspan="4">%s</td></tr>', $html);
        $rowHtml .= sprintf(
            '<tr id="row_%s"><td class="label config-vertical-label">%s</td><td class="value">%s</td></tr>',
            $element->getHtmlId(),
            $element->getLabelHtml($element->getHtmlId(), "[WEBSITE]"),
            $element->getElementHtml()
        );
        return $rowHtml;
    }
}
