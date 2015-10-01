<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item\Repository;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemDataFactoryMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->quoteRepositoryMock =
            $this->getMock('\Magento\Quote\Model\QuoteRepository', [], [], '', false);
        $this->productRepositoryMock =
            $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface', [], [], '', false);
        $this->itemDataFactoryMock =
            $this->getMock('Magento\Quote\Api\Data\CartItemInterfaceFactory', ['create'], [], '', false);
        $this->dataMock = $this->getMock('Magento\Quote\Model\Quote\Item', [], [], '', false);
        $this->quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $methods = ['getId', 'getSku', 'getQty', 'setData', '__wakeUp', 'getProduct', 'addProduct'];
        $this->quoteItemMock =
            $this->getMock('Magento\Quote\Model\Quote\Item', $methods, [], '', false);

        $this->repository = new Repository(
            $this->quoteRepositoryMock,
            $this->productRepositoryMock,
            $this->itemDataFactoryMock
        );
    }

    /**
     * @param null|string|bool|int|float $value
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of
     * @dataProvider addItemWithInvalidQtyDataProvider
     */
    public function testSaveItemWithInvalidQty($value)
    {
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue($value));
        $this->repository->save($this->dataMock);
    }

    /**
     * @return array
     */
    public function addItemWithInvalidQtyDataProvider()
    {
        return [
            ['string'],
            [0],
            [''],
            [null],
            [-12],
            [false],
            [-13.1],
        ];
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please specify all the required information.
     */
    public function testSaveCouldNotAddProduct()
    {
        $cartId = 13;
        $this->dataMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->productMock));
        $this->dataMock->expects($this->once())->method('getSku');
        $this->quoteMock
            ->expects($this->once())
            ->method('addProduct')
            ->with($this->productMock, 12)
            ->willReturn('Please specify all the required information.');
        $this->quoteMock->expects($this->never())->method('getItemById');
        $this->quoteRepositoryMock->expects($this->never())->method('save')->with($this->quoteMock);
        $this->quoteMock
            ->expects($this->never())
            ->method('getAllItems');
        $this->repository->save($this->dataMock);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save quote
     */
    public function testSaveCouldNotSaveException()
    {
        $cartId = 13;
        $this->dataMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->productMock));
        $this->dataMock->expects($this->once())->method('getSku');
        $this->quoteMock
            ->expects($this->once())
            ->method('addProduct')
            ->with($this->productMock, 12)
            ->willReturn($this->productMock);
        $this->quoteMock->expects($this->never())->method('getItemById');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue(null));
        $exceptionMessage = 'Could not save quote';
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__($exceptionMessage));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);
        $this->quoteMock
            ->expects($this->never())
            ->method('getAllItems');
        $this->repository->save($this->dataMock);
    }
    /**
     * @return void
     */
    public function testSave()
    {
        $cartId = 13;
        $this->dataMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->productMock));
        $this->dataMock->expects($this->once())->method('getSku');
        $this->quoteMock
            ->expects($this->once())
            ->method('addProduct')
            ->with($this->productMock, 12)
            ->willReturn($this->productMock);
        $this->quoteMock->expects($this->never())->method('getItemById');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue(null));
        $this->quoteMock
            ->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->quoteItemMock]);
        $this->quoteItemMock->expects($this->any())->method('getId');
        $this->assertEquals($this->quoteItemMock, $this->repository->save($this->dataMock));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart 11 doesn't contain item  5
     */
    public function testUpdateItemWithInvalidQuoteItem()
    {
        $cartId = 11;
        $itemId = 5;
        $this->dataMock->expects($this->once())->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue($itemId));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue(false));
        $this->quoteItemMock->expects($this->never())->method('setData');
        $this->quoteItemMock->expects($this->never())->method('addProduct');

        $this->repository->save($this->dataMock);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save quote
     */
    public function testUpdateItemWithCouldNotSaveException()
    {
        $cartId = 11;
        $itemId = 5;
        $this->dataMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue($itemId));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->any())->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->quoteItemMock->expects($this->once())->method('setData')->with('qty', 12);
        $this->productRepositoryMock
            ->expects($this->never())->method('get');
        $this->quoteItemMock->expects($this->never())->method('addProduct');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteMock
            ->expects($this->never())
            ->method('getAllItems');
        $this->quoteItemMock->expects($this->never())->method('getId');
        $exceptionMessage = 'Could not save quote';
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__($exceptionMessage));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);

        $this->repository->save($this->dataMock);
    }

    /**
     * @return void
     */
    public function testUpdateItemQty()
    {
        $cartId = 11;
        $itemId = 5;
        $this->dataMock->expects($this->exactly(2))->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue($itemId));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->dataMock->expects($this->once())->method('getId')->willReturn($itemId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->quoteItemMock->expects($this->once())->method('setData')->with('qty', 12);
        $this->productRepositoryMock
            ->expects($this->never())->method('get');
        $this->quoteItemMock->expects($this->never())->method('addProduct');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock
            ->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->quoteItemMock]);
        $this->quoteItemMock->expects($this->any())->method('getId')->willReturn($itemId);
        $this->assertEquals($this->quoteItemMock, $this->repository->save($this->dataMock));
    }

    /**
     * @return void
     */
    public function testUpdateItemOptions()
    {
        $cartId = 11;
        $itemId = 5;
        $cartItemProcessorMock = $this->getMock('\Magento\Quote\Model\Quote\Item\CartItemProcessorInterface');
        $this->repository = new Repository(
            $this->quoteRepositoryMock,
            $this->productRepositoryMock,
            $this->itemDataFactoryMock,
            ['simple' => $cartItemProcessorMock]
        );
        $requestMock = $this->getMock('\Magento\Framework\DataObject', ['setQty'], [], '', false);
        $cartItemProcessorMock->expects($this->once())->method('convertToBuyRequest')->willReturn($requestMock);
        $cartItemProcessorMock
            ->expects($this->once())
            ->method('processProductOptions')
            ->willReturn($this->quoteItemMock);
        $requestMock->expects($this->once())->method('setQty')->with(12)->willReturnSelf();
        $this->quoteMock
            ->expects($this->once())
            ->method('updateItem')
            ->with($itemId, $requestMock)
            ->willReturn($this->quoteItemMock);
        $this->dataMock->expects($this->any())->method('getQty')->will($this->returnValue(12));
        $this->dataMock->expects($this->once())->method('getItemId')->will($this->returnValue($itemId));
        $this->dataMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->productRepositoryMock
            ->expects($this->never())->method('get');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock
            ->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->quoteItemMock]);
        $this->quoteItemMock->expects($this->any())->method('getId')->willReturn($itemId);
        $this->quoteItemMock->expects($this->any())->method('getQty')->willReturn(12);
        $this->assertEquals($this->quoteItemMock, $this->repository->save($this->dataMock));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart 11 doesn't contain item  5
     */
    public function testDeleteWithInvalidQuoteItem()
    {
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue(false));
        $this->quoteMock->expects($this->never())->method('removeItem');

        $this->repository->deleteById($cartId, $itemId);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not remove item from quote
     */
    public function testDeleteWithCouldNotSaveException()
    {
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteMock->expects($this->once())
            ->method('removeItem')->with($itemId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $exceptionMessage = 'Could not remove item from quote';
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__($exceptionMessage));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);

        $this->repository->deleteById($cartId, $itemId);
    }

    /**
     * @return void
     */
    public function testGetList()
    {
        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')
            ->with(33)
            ->will($this->returnValue($quoteMock));
        $itemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', [], [], '', false);
        $quoteMock->expects($this->any())->method('getAllItems')->will($this->returnValue([$itemMock]));

        $this->assertEquals([$itemMock], $this->repository->getList(33));
    }

    /**
     * @return void
     */
    public function testDeleteById()
    {
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue($this->quoteItemMock));
        $this->quoteMock->expects($this->once())->method('removeItem');
        $this->quoteMock->expects($this->once())->method('collectTotals')->will($this->returnValue($this->quoteMock));
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);

        $this->assertTrue($this->repository->deleteById($cartId, $itemId));
    }
}
