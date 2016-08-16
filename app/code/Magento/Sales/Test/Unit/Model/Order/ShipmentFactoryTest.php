<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

/**
 * Unit test for shipment factory class.
 */
class ShipmentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $subject;

    /**
     * Order converter mock.
     *
     * @var \Magento\Sales\Model\Convert\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $converter;

    /**
     * Shipment track factory mock.
     *
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $trackFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentValidatorMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->converter = $this->getMock(
            \Magento\Sales\Model\Convert\Order::class,
            ['toShipment', 'itemToShipmentItem'],
            [],
            '',
            false
        );

        $convertOrderFactory = $this->getMock(
            \Magento\Sales\Model\Convert\OrderFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $convertOrderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->converter);

        $this->trackFactory = $this->getMock(
            \Magento\Sales\Model\Order\Shipment\TrackFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->shipmentValidatorMock = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface::class
        )->getMockForAbstractClass();

        $this->subject = $objectManager->getObject(
            \Magento\Sales\Model\Order\ShipmentFactory::class,
            [
                'convertOrderFactory' => $convertOrderFactory,
                'trackFactory' => $this->trackFactory
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $this->subject,
            'shipmentValidator',
            $this->shipmentValidatorMock
        );
    }

    /**
     * @param array|null $tracks
     * @dataProvider createDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreate($tracks)
    {
        $orderItem = $this->getMock(
            \Magento\Sales\Model\Order\Item::class,
            ['getId', 'getQtyOrdered'],
            [],
            '',
            false
        );
        $orderItem->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $orderItem->expects($this->any())
            ->method('getQtyOrdered')
            ->willReturn(5);

        $shipmentItem = $this->getMock(
            \Magento\Sales\Model\Order\Shipment\Item::class,
            ['setQty'],
            [],
            '',
            false
        );
        $shipmentItem->expects($this->once())
            ->method('setQty')
            ->with(5);

        $order = $this->getMock(
            \Magento\Sales\Model\Order::class,
            ['getAllItems'],
            [],
            '',
            false
        );
        $order->expects($this->any())
            ->method('getAllItems')
            ->willReturn([$orderItem]);

        $shipment = $this->getMock(
            \Magento\Sales\Model\Order\Shipment::class,
            ['addItem', 'setTotalQty', 'addTrack'],
            [],
            '',
            false
        );
        $shipment->expects($this->once())
            ->method('addItem')
            ->with($shipmentItem);
        $shipment->expects($this->once())
            ->method('setTotalQty')
            ->with(5)
            ->willReturn($shipment);

        $this->converter->expects($this->any())
            ->method('toShipment')
            ->with($order)
            ->willReturn($shipment);
        $this->converter->expects($this->any())
            ->method('itemToShipmentItem')
            ->with($orderItem)
            ->willReturn($shipmentItem);

        if ($tracks) {
            $shipmentTrack = $this->getMock(
                \Magento\Sales\Model\Order\Shipment\Track::class,
                ['addData'],
                [],
                '',
                false
            );

            if (empty($tracks[0]['number'])) {
                $shipmentTrack->expects($this->never())
                    ->method('addData');

                $this->trackFactory->expects($this->never())
                    ->method('create');

                $shipment->expects($this->never())
                    ->method('addTrack');

                $this->setExpectedException(
                    \Magento\Framework\Exception\LocalizedException::class
                );
            } else {
                $shipmentTrack->expects($this->once())
                    ->method('addData')
                    ->willReturnSelf();

                $this->trackFactory->expects($this->once())
                    ->method('create')
                    ->willReturn($shipmentTrack);

                $shipment->expects($this->once())
                    ->method('addTrack')
                    ->with($shipmentTrack);
            }
        }

        $this->shipmentValidatorMock->expects($this->once())->method('validate')->willReturn([]);

        $this->assertEquals($shipment, $this->subject->create($order, ['1' => 5], $tracks));
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            [null],
            [[['number' => 'TEST_TRACK']]],
            [[['number' => '']]],
        ];
    }
}
