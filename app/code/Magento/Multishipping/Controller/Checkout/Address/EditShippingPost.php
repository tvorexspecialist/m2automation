<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout\Address;

/**
 * Class \Magento\Multishipping\Controller\Checkout\Address\EditShippingPost
 *
 * @since 2.0.0
 */
class EditShippingPost extends \Magento\Multishipping\Controller\Checkout\Address
{
    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        if ($addressId = $this->getRequest()->getParam('id')) {
            $this->_objectManager->create(
                \Magento\Multishipping\Model\Checkout\Type\Multishipping::class
            )->updateQuoteCustomerShippingAddress(
                $addressId
            );
        }
        $this->_redirect('*/checkout/shipping');
    }
}
