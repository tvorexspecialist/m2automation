<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller;

class ProductTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testListActionDesign()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $product = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->get('custom-design-simple-product');
        $this->getRequest()->setParam('id', $product->getId());
        $this->dispatch('review/product/listAction');
        $result = $this->getResponse()->getBody();
        $this->assertNotContains("/frontend/Magento/luma/en_US/", $result);
    }
}
