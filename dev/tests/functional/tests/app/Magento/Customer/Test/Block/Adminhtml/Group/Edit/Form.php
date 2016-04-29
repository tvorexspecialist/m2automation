<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml\Group\Edit;

/**
 * Customer group edit form.
 */
class Form extends \Magento\Mtf\Block\Form
{
    /**
     *
     *
     * @param string $field
     * @return bool
     */
    public function isFieldDisabled($field)
    {
        return $this->_rootElement->find($this->mapping[$field]['selector'])->isDisabled();
    }
}
