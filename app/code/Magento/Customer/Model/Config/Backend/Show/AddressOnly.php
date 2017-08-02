<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config\Backend\Show;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Customer Show Address Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.2.0
 */
class AddressOnly extends Customer
{
    /**
     * Retrieve attribute objects
     *
     * @return AbstractAttribute[]
     * @since 2.2.0
     */
    protected function _getAttributeObjects()
    {
        return [$this->_eavConfig->getAttribute('customer_address', $this->_getAttributeCode())];
    }
}
