<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Http\Client;

use Braintree\Result\Successful;
use Magento\BraintreeTwo\Gateway\Http\Client\TransactionSubmitForSettlement;
use Magento\BraintreeTwo\Model\Adapter\BraintreeTransaction;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class TransactionSubmitForSettlementTest
 */
class TransactionSubmitForSettlementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransactionSubmitForSettlement
     */
    private $client;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var BraintreeTransaction|\PHPUnit_Framework_MockObject_MockObject
     */
    private $braintreeTransaction;

    protected function setUp()
    {
        $this->logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();
        $this->braintreeTransaction = $this->getMockBuilder(BraintreeTransaction::class)
            ->disableOriginalConstructor()
            ->setMethods(['submitForSettlement'])
            ->getMock();

        $this->client = new TransactionSubmitForSettlement($this->logger, $this->braintreeTransaction);
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Http\Client\TransactionSubmitForSettlement::placeRequest
     * @expectedException \Magento\Payment\Gateway\Http\ClientException
     * @expectedExceptionMessage Transaction has been declined
     */
    public function testPlaceRequestWithException()
    {
        $exception = new \Exception('Transaction has been declined');
        $this->braintreeTransaction->expects(static::once())
            ->method('submitForSettlement')
            ->willThrowException($exception);

        /** @var TransferInterface|\PHPUnit_Framework_MockObject_MockObject $transferObjectMock */
        $transferObjectMock = $this->getTransferObjectMock();
        $this->client->placeRequest($transferObjectMock);
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Http\Client\TransactionSubmitForSettlement::placeRequest
     */
    public function testPlaceRequest()
    {
        $data = new Successful(['success'], [true]);
        $this->braintreeTransaction->expects(static::once())
            ->method('submitForSettlement')
            ->willReturn($data);

        /** @var TransferInterface|\PHPUnit_Framework_MockObject_MockObject $transferObjectMock */
        $transferObjectMock = $this->getTransferObjectMock();
        $response = $this->client->placeRequest($transferObjectMock);
        static::assertTrue(is_object($response['object']));
        static::assertEquals(['object' => $data], $response);
    }

    /**
     * @return TransferInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTransferObjectMock()
    {
        $mock = $this->getMock(TransferInterface::class);
        $mock->expects($this->once())
            ->method('getBody')
            ->willReturn([
                'transaction_id' => 'vb4c6b',
                'amount' => 124.00
            ]);

        return $mock;
    }
}
