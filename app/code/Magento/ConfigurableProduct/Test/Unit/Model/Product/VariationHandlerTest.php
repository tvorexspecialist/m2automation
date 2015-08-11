<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product;

use Magento\ConfigurableProduct\Model\Product\VariationHandler;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class VariationHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VariationHandler
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_attributeSetFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\EntityFactory
     */
    protected $_entityFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $_stockConfiguration;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ConfigurableProduct\Model\Product\Type\Configurable*/
    protected $configurableProduct;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->jsonHelperMock = $this->getMock(
            'Magento\Framework\Json\Helper\Data',
            ['jsonDecode'],
            [],
            '',
            false
        );
        $this->_productFactoryMock = $this->getMock(
            'Magento\Catalog\Model\ProductFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_entityFactoryMock = $this->getMock(
            'Magento\Eav\Model\EntityFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_attributeSetFactory = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\SetFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_stockConfiguration = $this->getMock(
            'Magento\CatalogInventory\Api\StockConfigurationInterface',
            [],
            [],
            '',
            false
        );
        $this->configurableProduct = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            [],
            [],
            '',
            false
        );

        $this->_model = $this->_objectHelper->getObject(
            'Magento\ConfigurableProduct\Model\Product\VariationHandler',
            [
                'productFactory' => $this->_productFactoryMock,
                'entityFactory' => $this->_entityFactoryMock,
                'attributeSetFactory' => $this->_attributeSetFactory,
                'jsonHelper' => $this->jsonHelperMock,
                'stockConfiguration' => $this->_stockConfiguration,
                'configurableProduct' => $this->configurableProduct
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGenerateSimpleProducts()
    {
        $productsData = [
            6 =>
                [
                    'image' => 'image.jpg',
                    'name' => 'config-red',
                    'configurable_attribute' => '{"new_attr":"6"}',
                    'sku' => 'config-red',
                    'quantity_and_stock_status' =>
                        [
                            'qty' => '',
                        ],
                    'weight' => '333',
                ]
        ];
        $stockData = [
            'manage_stock' => '0',
            'use_config_enable_qty_increments' => '1',
            'use_config_qty_increments' => '1',
            'use_config_manage_stock' => 0,
            'is_decimal_divided' => 0
        ];

        $parentProductMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(
                [
                    '__wakeup',
                    'getNewVariationsAttributeSetId',
                    'getStockData',
                    'getQuantityAndStockStatus',
                    'getWebsiteIds'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $newSimpleProductMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(
                [
                    '__wakeup',
                    'save',
                    'getId',
                    'setStoreId',
                    'setTypeId',
                    'setAttributeSetId',
                    'getTypeInstance',
                    'getStoreId',
                    'addData',
                    'setWebsiteIds',
                    'setStatus',
                    'setVisibility'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute')
            ->setMethods(['isInSet', 'setAttributeSetId', 'setAttributeGroupId', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Set')
            ->setMethods(['load', 'addSetInfo', 'getDefaultGroupId'])
            ->disableOriginalConstructor()
            ->getMock();
        $eavEntityMock = $this->getMockBuilder('\Magento\Eav\Model\Entity')
            ->setMethods(['setType', 'getTypeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Type')
            ->setMethods(['getEditableAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $editableAttributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->setMethods(['getIsUnique', 'getAttributeCode', 'getFrontend', 'getIsVisible'])
            ->disableOriginalConstructor()
            ->getMock();
        $frontendAttributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Frontend')
            ->setMethods(['getInputType'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurableProduct->expects($this->once())->method('getUsedProductAttributes')
            ->willReturn([$attributeMock]);
        $parentProductMock->expects($this->any())
            ->method('getNewVariationsAttributeSetId')
            ->willReturn('new_attr_set_id');
        $this->_attributeSetFactory->expects($this->once())->method('create')->willReturn($attributeSetMock);
        $attributeSetMock->expects($this->once())->method('load')->with('new_attr_set_id')->willReturnSelf();
        $this->_entityFactoryMock->expects($this->once())->method('create')->willReturn($eavEntityMock);
        $eavEntityMock->expects($this->once())->method('setType')->with('catalog_product')->willReturnSelf();
        $eavEntityMock->expects($this->once())->method('getTypeId')->willReturn('type_id');
        $attributeSetMock->expects($this->once())->method('addSetInfo')->with('type_id', [$attributeMock]);
        $attributeMock->expects($this->once())->method('isInSet')->with('new_attr_set_id')->willReturn(false);
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with('new_attr_set_id')->willReturnSelf();
        $attributeSetMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->with('new_attr_set_id')
            ->willReturn('default_group_id');
        $attributeMock->expects($this->once())
            ->method('setAttributeGroupId')
            ->with('default_group_id')
            ->willReturnSelf();
        $attributeMock->expects($this->once())->method('save')->willReturnSelf();
        $this->_productFactoryMock->expects($this->once())->method('create')->willReturn($newSimpleProductMock);
        $this->jsonHelperMock->expects($this->once())
            ->method('jsonDecode')
            ->with('{"new_attr":"6"}')
            ->willReturn(['new_attr' => 6]);
        $newSimpleProductMock->expects($this->once())->method('setStoreId')->with(0)->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('setTypeId')->with('simple')->willReturnSelf();
        $newSimpleProductMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with('new_attr_set_id')
            ->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('getTypeInstance')->willReturn($productTypeMock);
        $productTypeMock->expects($this->once())
            ->method('getEditableAttributes')
            ->with($newSimpleProductMock)
            ->willReturn([$editableAttributeMock]);
        $editableAttributeMock->expects($this->once())->method('getIsUnique')->willReturn(false);
        $editableAttributeMock->expects($this->once())->method('getAttributeCode')->willReturn('some_code');
        $editableAttributeMock->expects($this->any())->method('getFrontend')->willReturn($frontendAttributeMock);
        $frontendAttributeMock->expects($this->any())->method('getInputType')->willReturn('input_type');
        $editableAttributeMock->expects($this->any())->method('getIsVisible')->willReturn(false);
        $parentProductMock->expects($this->once())->method('getStockData')->willReturn($stockData);
        $parentProductMock->expects($this->once())
            ->method('getQuantityAndStockStatus')
            ->willReturn(['is_in_stock' => 1]);
        $newSimpleProductMock->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $this->_stockConfiguration->expects($this->once())
            ->method('getManageStock')
            ->with('store_id')
            ->willReturn(1);
        $newSimpleProductMock->expects($this->once())->method('addData')->willReturnSelf();
        $parentProductMock->expects($this->once())->method('getWebsiteIds')->willReturn('website_id');
        $newSimpleProductMock->expects($this->once())->method('setWebsiteIds')->with('website_id')->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('setVisibility')->with(1)->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('save')->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('getId')->willReturn('product_id');

        $this->assertEquals(['product_id'], $this->_model->generateSimpleProducts($parentProductMock, $productsData));
    }
}
