<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Block;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class \Magento\Rule\Block\Actions
 *
 */
class Actions implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if ($element->getRule() && $element->getRule()->getActions()) {
            return $element->getRule()->getActions()->asHtmlRecursive();
        }
        return '';
    }
}
