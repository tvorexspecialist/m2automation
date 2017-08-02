<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\System\Config;

/**
 * VAT validation controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
abstract class Validatevat extends \Magento\Backend\App\Action
{
    /**
     * Perform customer VAT ID validation
     *
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    protected function _validate()
    {
        return $this->_objectManager->get(\Magento\Customer\Model\Vat::class)
            ->checkVatNumber(
                $this->getRequest()->getParam('country'),
                $this->getRequest()->getParam('vat')
            );
    }
}
