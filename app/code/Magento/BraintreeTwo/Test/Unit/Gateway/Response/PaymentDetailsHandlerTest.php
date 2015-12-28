<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Response;

use Braintree\Transaction;
use Magento\BraintreeTwo\Gateway\Response\PaymentDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\BraintreeTwo\Gateway\Helper\SubjectReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PaymentDetailsHandlerTest
 */
class PaymentDetailsHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TRANSACTION_ID = '432erwwe';

    /**
     * @var \Magento\BraintreeTwo\Gateway\Response\PaymentDetailsHandler
     */
    private $paymentHandler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    private $payment;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    protected function setUp()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setTransactionId',
                'setCcTransId',
                'setLastTransId',
                'setAdditionalInformation',
                'setIsTransactionClosed'
            ])
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment->expects(static::once())
            ->method('setTransactionId');
        $this->payment->expects(static::once())
            ->method('setCcTransId');
        $this->payment->expects(static::once())
            ->method('setLastTransId');
        $this->payment->expects(static::once())
            ->method('setIsTransactionClosed');
        $this->payment->expects(static::any())
            ->method('setAdditionalInformation');

        $this->paymentHandler = new PaymentDetailsHandler($this->subjectReaderMock);
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Response\PaymentDetailsHandler::handle
     */
    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $transaction = $this->getBraintreeTransaction();

        $subject = ['payment' => $paymentData];
        $response = ['object' => $transaction];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);
        $this->subjectReaderMock->expects(self::once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);

        $this->paymentHandler->handle($subject, $response);
    }

    /**
     * Create mock for payment data object and order payment
     * @return MockObject
     */
    private function getPaymentDataObjectMock()
    {
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
     * Create Braintree transaction
     * @return MockObject
     */
    private function getBraintreeTransaction()
    {
        $attributes = [
            'id' => self::TRANSACTION_ID,
            'avsPostalCodeResponseCode' => 'M',
            'avsStreetAddressResponseCode' => 'M',
            'cvvResponseCode' => 'M',
            'processorAuthorizationCode' => 'W1V8XK',
            'processorResponseCode' => '1000',
            'processorResponseText' => 'Approved'
        ];

        $transaction = \Braintree\Transaction::factory($attributes);

        return $transaction;
    }
}
