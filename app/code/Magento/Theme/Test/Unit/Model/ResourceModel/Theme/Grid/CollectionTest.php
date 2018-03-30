<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\ResourceModel\Theme\Grid;

use Magento\Theme\Model\ResourceModel\Theme\Grid\Collection;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Select;

/**
 * CollectionTest contains unit tests for \Magento\Theme\Model\ResourceModel\Theme\Grid\Collection class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EntityFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var AggregationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aggregationsMock;

    /**
     * @var Select
     */
    protected $selectMock;

    /**
     * @var Collection
     */
    protected $model;

    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMockBuilder(EntityFactoryInterface::class)
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->fetchStrategyMock = $this->getMockBuilder(FetchStrategyInterface::class)
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->resourceMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->aggregationsMock = $this->getMockBuilder(AggregationInterface::class)
            ->getMockForAbstractClass();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->model = (new ObjectManager($this))->getObject(Collection::class, [
            'entityFactory' => $this->entityFactoryMock,
            'logger' => $this->loggerMock,
            'fetchStrategy' => $this->fetchStrategyMock,
            'eventManager' => $this->eventManagerMock,
            'mainTable' => null,
            'eventPrefix' => 'test_event_prefix',
            'eventObject' => 'test_event_object',
            'resourceModel' => null,
            'resource' => $this->resourceMock,
        ]);
    }

    /**
     * @covers \Magento\Theme\Model\ResourceModel\Theme\Grid\Collection::setSearchCriteria
     * @covers \Magento\Theme\Model\ResourceModel\Theme\Grid\Collection::getAggregations
     */
    public function testSetGetAggregations()
    {
        $this->model->setAggregations($this->aggregationsMock);
        $this->assertInstanceOf(AggregationInterface::class, $this->model->getAggregations());
    }

    /**
     * @covers \Magento\Theme\Model\ResourceModel\Theme\Grid\Collection::setSearchCriteria
     */
    public function testSetSearchCriteria()
    {
        $this->assertEquals($this->model, $this->model->setSearchCriteria());
    }
}
