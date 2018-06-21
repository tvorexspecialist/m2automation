<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\ResourceModel\Selection;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Framework\DB\Select;

/**
 * Class CollectionTest.
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $universalFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entity;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection\Collection
     */
    private $model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->universalFactory = $this->getMockBuilder(UniversalFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entity = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapter = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory = $this->getMockBuilder(ProductLimitationFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->universalFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->entity);
        $this->entity->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapter);
        $this->entity->expects($this->any())
            ->method('getDefaultAttributes')
            ->willReturn([]);
        $this->adapter->expects($this->any())
            ->method('select')
            ->willReturn($this->select);

        $this->model = $objectManager->getObject(
            \Magento\Bundle\Model\ResourceModel\Selection\Collection::class,
            [
                'storeManager' => $this->storeManager,
                'universalFactory' => $this->universalFactory,
                'productLimitationFactory' => $factory
            ]
        );
    }

    public function testAddQuantityFilter()
    {
        $statusTableName = 'cataloginventory_stock_status';
        $itemTableName = 'cataloginventory_stock_item';
        $this->entity->expects($this->exactly(2))
            ->method('getTable')
            ->willReturnMap([
                ['cataloginventory_stock_item', $itemTableName],
                ['cataloginventory_stock_status', $statusTableName],
            ]);
        $this->select->expects($this->exactly(2))
            ->method('joinInner')
            ->withConsecutive(
                [
                    ['stock' => $statusTableName],
                    'selection.product_id = stock.product_id',
                    [],
                ],
                [
                    ['stock_item' => $itemTableName],
                    'selection.product_id = stock_item.product_id',
                    [],
                ]
            )->willReturnSelf();
        $this->select
            ->expects($this->exactly(2))
            ->method('where')
            ->withConsecutive(
                [
                    '('
                    . 'selection.selection_can_change_qty > 0'
                    . ' or '
                    . 'selection.selection_qty <= stock.qty'
                    . ' or '
                    .'stock_item.manage_stock = 0'
                    . ')',
                ],
                [
                    'stock.stock_status = 1',
                ]
            )->willReturnSelf();

        $this->assertEquals($this->model, $this->model->addQuantityFilter());
    }
}
