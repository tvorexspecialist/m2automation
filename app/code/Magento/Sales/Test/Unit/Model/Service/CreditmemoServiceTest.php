<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Service;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;

/**
 * Class CreditmemoServiceTest
 */
class CreditmemoServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoRepositoryMock;

    /**
     * @var \Magento\Sales\Api\CreditmemoCommentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoCommentRepositoryMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoNotifier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoNotifierMock;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var \Magento\Sales\Model\Service\CreditmemoService
     */
    protected $creditmemoService;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->creditmemoRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\CreditmemoRepositoryInterface',
            ['get'],
            '',
            false
        );
        $this->creditmemoCommentRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\CreditmemoCommentRepositoryInterface',
            [],
            '',
            false
        );
        $this->searchCriteriaBuilderMock = $this->getMock(
            'Magento\Framework\Api\SearchCriteriaBuilder',
            ['create', 'addFilters'],
            [],
            '',
            false
        );
        $this->filterBuilderMock = $this->getMock(
            'Magento\Framework\Api\FilterBuilder',
            ['setField', 'setValue', 'setConditionType', 'create'],
            [],
            '',
            false
        );
        $this->creditmemoNotifierMock = $this->getMock(
            'Magento\Sales\Model\Order\CreditmemoNotifier',
            [],
            [],
            '',
            false
        );
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)->getMockForAbstractClass();
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->creditmemoService = $objectManager->getObject(
            'Magento\Sales\Model\Service\CreditmemoService',
            [
                'creditmemoRepository' => $this->creditmemoRepositoryMock,
                'creditmemoCommentRepository' => $this->creditmemoCommentRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock,
                'creditmemoNotifier' => $this->creditmemoNotifierMock,
                'priceCurrency' => $this->priceCurrencyMock
            ]
        );
    }

    /**
     * Run test cancel method
     * @expectedExceptionMessage You can not cancel Credit Memo
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCancel()
    {
        $this->assertTrue($this->creditmemoService->cancel(1));
    }

    /**
     * Run test getCommentsList method
     */
    public function testGetCommentsList()
    {
        $id = 25;
        $returnValue = 'return-value';

        $filterMock = $this->getMock(
            'Magento\Framework\Api\Filter',
            [],
            [],
            '',
            false
        );
        $searchCriteriaMock = $this->getMock(
            'Magento\Framework\Api\SearchCriteria',
            [],
            [],
            '',
            false
        );

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with('parent_id')
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with($id)
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())
            ->method('setConditionType')
            ->with('eq')
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($filterMock));
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilters')
            ->with([$filterMock]);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchCriteriaMock));
        $this->creditmemoCommentRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->will($this->returnValue($returnValue));

        $this->assertEquals($returnValue, $this->creditmemoService->getCommentsList($id));
    }

    /**
     * Run test notify method
     */
    public function testNotify()
    {
        $id = 123;
        $returnValue = 'return-value';

        $modelMock = $this->getMockForAbstractClass(
            'Magento\Sales\Model\AbstractModel',
            [],
            '',
            false
        );

        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($modelMock));
        $this->creditmemoNotifierMock->expects($this->once())
            ->method('notify')
            ->with($modelMock)
        ->will($this->returnValue($returnValue));

        $this->assertEquals($returnValue, $this->creditmemoService->notify($id));
    }

    public function testRefund()
    {
        $creditMemoMock = $this->getMockBuilder('Magento\Sales\Api\Data\CreditmemoInterface')
            ->setMethods(['getId', 'getOrder', 'getBaseGrandTotal', 'getInvoice'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $creditMemoMock->expects($this->once())->method('getId')->willReturn(null);
        $orderMock = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();

        $creditMemoMock->expects($this->atLeastOnce())->method('getOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getBaseTotalRefunded')->willReturn(0);
        $orderMock->expects($this->once())->method('getBaseTotalPaid')->willReturn(10);
        $creditMemoMock->expects($this->once())->method('getBaseGrandTotal')->willReturn(10);

        $this->priceCurrencyMock->expects($this->any())
            ->method('round')
            ->willReturnArgument(0);

        // Set payment adapter dependency
        $paymentAdapterMock = $this->getMock('Magento\Sales\Model\Order\PaymentAdapterInterface', [], [], '', false);
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->creditmemoService,
            'paymentAdapter',
            $paymentAdapterMock
        );

        // Set resource dependency
        $resourceMock = $this->getMock('Magento\Framework\App\ResourceConnection', ['getConnection'], [], '', false);
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->creditmemoService,
            'resource',
            $resourceMock
        );

        // Set order repository dependency
        $orderRepositoryMock = $this->getMockBuilder('Magento\Sales\Api\OrderRepositoryInterface')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMockForAbstractClass();
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->creditmemoService,
            'orderRepository',
            $orderRepositoryMock
        );

        $adapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['beginTransaction', 'commit', 'rollBack'])
            ->getMockForAbstractClass();
        $resourceMock->expects($this->once())->method('getConnection')->with('sales')->willReturn($adapterMock);
        $adapterMock->expects($this->once())->method('beginTransaction');
        $paymentAdapterMock->expects($this->once())
            ->method('refund')
            ->with($creditMemoMock, $orderMock, false)
            ->willReturn($orderMock);
        $orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($orderMock);
        $creditMemoMock->expects($this->once())
            ->method('getInvoice')
            ->willReturn(null);
        $adapterMock->expects($this->once())->method('commit');
        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('save');

        $this->assertSame($creditMemoMock, $this->creditmemoService->refund($creditMemoMock, true));
    }

    /**
     * @expectedExceptionMessage The most money available to refund is 1.
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testRefundExpectsMoneyAvailableToReturn()
    {
        $baseGrandTotal = 10;
        $baseTotalRefunded = 9;
        $baseTotalPaid = 10;
        $creditMemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->setMethods(['getId', 'getOrder', 'getBaseGrandTotal', 'formatBasePrice'])
            ->getMockForAbstractClass();
        $creditMemoMock->expects($this->once())->method('getId')->willReturn(null);
        $orderMock = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $creditMemoMock->expects($this->atLeastOnce())->method('getOrder')->willReturn($orderMock);
        $creditMemoMock->expects($this->once())->method('getBaseGrandTotal')->willReturn($baseGrandTotal);
        $orderMock->expects($this->atLeastOnce())->method('getBaseTotalRefunded')->willReturn($baseTotalRefunded);
        $this->priceCurrencyMock->expects($this->exactly(2))->method('round')->withConsecutive(
            [$baseTotalRefunded + $baseGrandTotal],
            [$baseTotalPaid]
        )->willReturnOnConsecutiveCalls(
            $baseTotalRefunded + $baseGrandTotal,
            $baseTotalPaid
        );
        $orderMock->expects($this->atLeastOnce())->method('getBaseTotalPaid')->willReturn($baseTotalPaid);
        $baseAvailableRefund = $baseTotalPaid - $baseTotalRefunded;
        $orderMock->expects($this->once())->method('formatBasePrice')->with(
            $baseAvailableRefund
        )->willReturn($baseAvailableRefund);
        $this->creditmemoService->refund($creditMemoMock, true);
    }

    /**
     * @expectedExceptionMessage We cannot register an existing credit memo.
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testRefundDoNotExpectsId()
    {
        $creditMemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $creditMemoMock->expects($this->once())->method('getId')->willReturn(444);
        $this->creditmemoService->refund($creditMemoMock, true);
    }
}
