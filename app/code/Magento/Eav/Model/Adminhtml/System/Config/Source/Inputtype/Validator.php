<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype;

/**
 * Validator for check input type value
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Validator extends \Zend_Validate_InArray
{
    /**
     * Eav data
     *
     * @var \Magento\Eav\Helper\Data
     * @since 2.0.0
     */
    protected $_eavData = null;

    /**
     * @param \Magento\Eav\Helper\Data $eavData
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(\Magento\Eav\Helper\Data $eavData)
    {
        $this->_eavData = $eavData;
        //set data haystack
        $haystack = $this->_eavData->getInputTypesValidatorData();

        //reset message template and set custom
        $this->_messageTemplates = null;
        $this->_initMessageTemplates();

        //parent construct with options
        parent::__construct(['haystack' => $haystack, 'strict' => true]);
    }

    /**
     * Initialize message templates with translating
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initMessageTemplates()
    {
        if (!$this->_messageTemplates) {
            $this->_messageTemplates = [
                self::NOT_IN_ARRAY => __('Input type "%value%" not found in the input types list.'),
            ];
        }
        return $this;
    }

    /**
     * Add input type to haystack
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function addInputType($type)
    {
        if (!in_array((string)$type, $this->_haystack, true)) {
            $this->_haystack[] = (string)$type;
        }
        return $this;
    }
}
