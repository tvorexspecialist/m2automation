<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Validate URL
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Url;

/**
 * Class \Magento\Framework\Url\Validator
 *
 * @since 2.0.0
 */
class Validator extends \Zend_Validate_Abstract
{
    /**#@+
     * Error keys
     */
    const INVALID_URL = 'invalidUrl';
    /**#@-*/

    /**
     * Object constructor
     * @since 2.0.0
     */
    public function __construct()
    {
        // set translated message template
        $this->setMessage((string)new \Magento\Framework\Phrase("Invalid URL '%value%'."), self::INVALID_URL);
    }

    /**
     * Validation failure message template definitions
     *
     * @var array
     * @since 2.0.0
     */
    protected $_messageTemplates = [self::INVALID_URL => "Invalid URL '%value%'."];

    /**
     * Validate value
     *
     * @param string $value
     * @return bool
     * @since 2.0.0
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if (!\Zend_Uri::check($value)) {
            $this->_error(self::INVALID_URL);
            return false;
        }

        return true;
    }
}
