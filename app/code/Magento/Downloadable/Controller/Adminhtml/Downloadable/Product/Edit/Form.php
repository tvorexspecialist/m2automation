<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit;

/**
 * Class \Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Form
 *
 * @since 2.0.0
 */
class Form extends \Magento\Catalog\Controller\Adminhtml\Product\Edit
{
    /**
     * Load downloadable tab fieldsets
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_initProduct();
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                \Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable::class,
                'admin.product.downloadable.information'
            )->toHtml()
        );
    }
}
