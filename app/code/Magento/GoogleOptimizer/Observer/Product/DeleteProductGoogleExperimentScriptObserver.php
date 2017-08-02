<?php
/**
 * Google Experiment Product observer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Observer\Product;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\GoogleOptimizer\Observer\Product\DeleteProductGoogleExperimentScriptObserver
 *
 * @since 2.0.0
 */
class DeleteProductGoogleExperimentScriptObserver implements ObserverInterface
{
    /**
     * @var \Magento\GoogleOptimizer\Model\Code
     * @since 2.0.0
     */
    protected $_modelCode;

    /**
     * @param \Magento\GoogleOptimizer\Model\Code $modelCode
     * @since 2.0.0
     */
    public function __construct(\Magento\GoogleOptimizer\Model\Code $modelCode)
    {
        $this->_modelCode = $modelCode;
    }

    /**
     * Delete Product scripts after deleting product
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();
        $this->_modelCode->loadByEntityIdAndType(
            $product->getId(),
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PRODUCT,
            $product->getStoreId()
        );

        if ($this->_modelCode->getId()) {
            $this->_modelCode->delete();
        }
        return $this;
    }
}
