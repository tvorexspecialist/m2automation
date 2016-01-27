<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Model\Report;

use Magento\BraintreeTwo\Model\Adapter\BraintreeAdapter;
use Magento\BraintreeTwo\Model\Report\FilterMapper;
use Magento\BraintreeTwo\Model\Report\TransactionsCollection;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TransactionsCollectionTest
 *
 * Test for class \Magento\BraintreeTwo\Model\Report\TransactionsCollection
 */
class TransactionsCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BraintreeAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $braintreeAdapterMock;

    /**
     * @var EntityFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityFactoryMock;

    /**
     * @var FilterMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMapperMock;

    /**
     * @var DocumentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionMapMock;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->transactionMapMock = $this->getMockBuilder(DocumentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityFactoryMock = $this->getMockBuilder(EntityFactoryInterface::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterMapperMock = $this->getMockBuilder(FilterMapper::class)
            ->setMethods(['getFilter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->braintreeAdapterMock = $this->getMockBuilder(BraintreeAdapter::class)
            ->setMethods(['search'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get items
     */
    public function testGetItems()
    {
        $this->filterMapperMock->expects($this->once())
            ->method('getFilter')
            ->willReturn(new BraintreeSearchNodeStub());

        $this->braintreeAdapterMock->expects($this->once())
            ->method('search')
            ->willReturn(['transaction1', 'transaction2']);

        $this->entityFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->transactionMapMock);

        $collection = new TransactionsCollection(
            $this->entityFactoryMock,
            $this->braintreeAdapterMock,
            $this->filterMapperMock
        );

        $collection->addFieldToFilter('orderId', ['like' => '0']);
        $items = $collection->getItems();
        $this->assertEquals(2, count($items));
        $this->assertInstanceOf(DocumentInterface::class, $items[1]);
    }

    /**
     * Get empty result
     */
    public function testGetItemsEmptyCollection()
    {
        $this->filterMapperMock->expects($this->once())
            ->method('getFilter')
            ->willReturn(new BraintreeSearchNodeStub());

        $this->braintreeAdapterMock->expects($this->once())
            ->method('search')
            ->willReturn(null);

        $this->entityFactoryMock->expects($this->never())
            ->method('create')
            ->willReturn($this->transactionMapMock);

        $collection = new TransactionsCollection(
            $this->entityFactoryMock,
            $this->braintreeAdapterMock,
            $this->filterMapperMock
        );

        $collection->addFieldToFilter('orderId', ['like' => '0']);
        $items = $collection->getItems();
        $this->assertEquals(0, count($items));
    }

    /**
     * Get items with limit
     */
    public function testGetItemsWithLimit()
    {
        $transations = range(1, TransactionsCollection::TRANSACTION_MAXIMUM_COUNT + 10);

        $this->filterMapperMock->expects($this->once())
            ->method('getFilter')
            ->willReturn(new BraintreeSearchNodeStub());

        $this->braintreeAdapterMock->expects($this->once())
            ->method('search')
            ->willReturn($transations);

        $this->entityFactoryMock->expects($this->exactly(TransactionsCollection::TRANSACTION_MAXIMUM_COUNT))
            ->method('create')
            ->willReturn($this->transactionMapMock);

        $collection = new TransactionsCollection(
            $this->entityFactoryMock,
            $this->braintreeAdapterMock,
            $this->filterMapperMock
        );

        $collection->addFieldToFilter('orderId', ['like' => '0']);
        $items = $collection->getItems();
        $this->assertEquals(TransactionsCollection::TRANSACTION_MAXIMUM_COUNT, count($items));
        $this->assertInstanceOf(DocumentInterface::class, $items[1]);
    }
}
