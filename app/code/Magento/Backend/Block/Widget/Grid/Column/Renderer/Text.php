<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\DataObject;

/**
 * Backend grid item renderer
 */
class Text extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Format variables pattern
     *
     * @var string
     */
    protected $_variablePattern = '/\\$([a-z0-9_]+)/i';

    /**
     * Get value for the cel
     *
     * @param DataObject $row
     * @return string
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        if ($this->getColumn()->getFormat() === null) {
            return $this->getSimpleValue($row);
        }
        return $this->getFormattedValue($row);
    }

    /**
     * Get simple value
     *
     * @param DataObject $row
     * @return string
     */
    private function getSimpleValue($row)
    {
        $data = parent::_getValue($row);
        $value = null === $data ? $this->getColumn()->getDefault() : $data;
        if (true === $this->getColumn()->getTranslate()) {
            $value = __($value);
        }
        return $this->escapeHtml($value);
    }

    /**
     * Replace placeholders in the string with values
     *
     * @param DataObject $row
     * @return string
     */
    private function getFormattedValue($row)
    {
        $value = $this->getColumn()->getFormat() ?: null;
        if (true === $this->getColumn()->getTranslate()) {
            $value = __($value);
        }
        if (preg_match_all($this->_variablePattern, $value, $matches)) {
            foreach ($matches[0] as $index => $match) {
                $replacement = $row->getData($matches[1][$index]);
                $value = str_replace($match, $replacement, $value);
            }
        }
        return $this->escapeHtml($value);
    }
}
