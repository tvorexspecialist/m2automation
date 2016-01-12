<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Command;

use Magento\BraintreeTwo\Gateway\Command\CaptureStrategyCommand;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory;
use Magento\BraintreeTwo\Gateway\Helper\SubjectReader;

/**
 * Class CaptureStrategyCommandTest
 */
class CaptureStrategyCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\BraintreeTwo\Gateway\Command\CaptureStrategyCommand
     */
    private $strategyCommand;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandPool;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionRepository;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var \Magento\Payment\Gateway\Command\GatewayCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $command;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    protected function setUp()
    {
        $this->commandPool = $this->getMockBuilder(CommandPoolInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', '__wakeup'])
            ->getMock();

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->initCommandMock();
        $this->initTransactionRepositoryMock();
        $this->initFilterBuilderMock();
        $this->initSearchCriteriaBuilderMock();

        $this->strategyCommand = new CaptureStrategyCommand(
            $this->commandPool,
            $this->transactionRepository,
            $this->filterBuilder,
            $this->searchCriteriaBuilder,
            $this->subjectReaderMock
        );
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Command\CaptureStrategyCommand::execute
     */
    public function testSaleExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);

        $this->payment->expects(static::once())
            ->method('getAuthorizationTransaction')
            ->willReturn(false);

        $this->payment->expects(static::never())
            ->method('getId');

        $this->commandPool->expects(static::once())
            ->method('get')
            ->with(CaptureStrategyCommand::SALE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    /**
     * @param int $size
     * @param string $command
     * @covers       \Magento\BraintreeTwo\Gateway\Command\CaptureStrategyCommand::execute
     * @dataProvider captureDataProvider
     */
    public function testCaptureExecute($size, $command)
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);

        $this->payment->expects(static::once())
            ->method('getAuthorizationTransaction')
            ->willReturn(true);

        $this->payment->expects(static::once())
            ->method('getId')
            ->willReturn(1);

        $this->filterBuilder->expects(static::exactly(2))
            ->method('setField')
            ->willReturnSelf();
        $this->filterBuilder->expects(static::exactly(2))
            ->method('setValue')
            ->willReturnSelf();

        $searchCriteria = new SearchCriteria();
        $this->searchCriteriaBuilder->expects(static::once())
            ->method('addFilters')
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects(static::once())
            ->method('create')
            ->willReturn($searchCriteria);

        $this->transactionRepository->expects(static::once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturnSelf();

        $this->transactionRepository->expects(static::once())
            ->method('getTotalCount')
            ->willReturn($size);

        $this->commandPool->expects(static::once())
            ->method('get')
            ->with($command)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    /**
     * Return variations for command testing
     */
    public function captureDataProvider()
    {
        return [
            ['collectionSize' => 0, 'command' => CaptureStrategyCommand::CAPTURE],
            ['collectionSize' => 1, 'command' => CaptureStrategyCommand::CLONE_TRANSACTION]
        ];
    }

    /**
     * Create mock for payment data object and order payment
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAuthorizationTransaction', 'getId'])
            ->getMock();

        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        return $mock;
    }

    /**
     * Create mock for gateway command object
     */
    private function initCommandMock()
    {
        $this->command = $this->getMockBuilder(GatewayCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $this->command->expects(static::once())
            ->method('execute')
            ->willReturn([]);
    }

    /**
     * Create mock for filter object
     */
    private function initFilterBuilderMock()
    {
        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setField', 'setValue', 'create', '__wakeup'])
            ->getMock();
    }

    /**
     * Create mock for search criteria object
     */
    private function initSearchCriteriaBuilderMock()
    {
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilters', 'create', '__wakeup'])
            ->getMock();
    }

    /**
     * Create mock for transaction repository
     */
    private function initTransactionRepositoryMock()
    {
        $this->transactionRepository = $this->getMockBuilder(TransactionRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList', 'getTotalCount', 'delete', 'get', 'save', 'create', '__wakeup'])
            ->getMock();
    }
}
