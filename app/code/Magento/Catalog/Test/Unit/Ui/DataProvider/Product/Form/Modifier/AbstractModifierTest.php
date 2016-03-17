<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class AbstractDataProviderTest
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractModifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModifierInterface
     */
    private $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var LocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $locatorMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var ArrayManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $arrayManagerMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->locatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods([
                'getStoreId',
                'getResource',
                'getData',
                'getAttributes',
                'getStore',
                'getAttributeDefaultValue',
                'getExistsStoreValueFlag'
            ])->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['load', 'getId'])
            ->getMockForAbstractClass();
        $this->arrayManagerMock = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->arrayManagerMock->expects($this->any())
            ->method('replace')
            ->willReturnArgument(1);
        $this->arrayManagerMock->expects($this->any())
            ->method('get')
            ->willReturnArgument(2);
        $this->arrayManagerMock->expects($this->any())
            ->method('set')
            ->willReturnArgument(1);
        $this->arrayManagerMock->expects($this->any())
            ->method('merge')
            ->willReturnArgument(1);
        $this->arrayManagerMock->expects($this->any())
            ->method('remove')
            ->willReturnArgument(1);

        $this->locatorMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->locatorMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
    }

    /**
     * @return ModifierInterface
     */
    abstract protected function createModel();

    /**
     * @return ModifierInterface
     */
    protected function getModel()
    {
        if (null === $this->model) {
            $this->model = $this->createModel();
        }

        return $this->model;
    }

    /**
     * @return array
     */
    protected function getSampleData()
    {
        return ['data_key' => 'data_value'];
    }
}
