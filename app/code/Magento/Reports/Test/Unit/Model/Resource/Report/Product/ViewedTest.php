<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\Resource\Report\Product;

use Magento\Reports\Model\Resource\Report\Product\Viewed;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\Resource\Report\Product\Viewed
     */
    protected $viewed;

    /**
     * @var \Magento\Framework\Model\Resource\Db\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezoneMock;

    /**
     * @var \Magento\Reports\Model\FlagFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flagFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTimeMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Catalog\Model\Resource\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Reports\Model\Resource\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Zend_Db_Statement_Interface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $zendDbMock;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendMock;

    /**
     * @var \Magento\Reports\Model\Flag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flagMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    protected function setUp()
    {
        $this->zendDbMock = $this->getMockBuilder('Zend_Db_Statement_Interface')->getMock();
        $this->zendDbMock->expects($this->any())->method('fetchColumn')->willReturn([]);

        $this->selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'from',
                    'where',
                    'joinInner',
                    'joinLeft',
                    'having',
                    'useStraightJoin',
                    'insertFromSelect',
                    '__toString'
                ]
            )
            ->getMock();
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('joinInner')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('joinLeft')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('having')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('useStraightJoin')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('insertFromSelect')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('__toString')->willReturn('string');

        $this->connectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')->getMock();
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->selectMock);
        $this->connectionMock->expects($this->any())->method('query')->willReturn($this->zendDbMock);

        $this->resourceMock = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->any())->method('getTableName')->will(
            $this->returnCallback(
                function ($arg) {
                    return $arg;
                }
            )
        );

        $this->contextMock = $this->getMockBuilder('Magento\Framework\Model\Resource\Db\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getResources')->willReturn($this->resourceMock);

        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $dateTime = $this->getMockBuilder('DateTime')->getMock();

        $this->timezoneMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')->getMock();
        $this->timezoneMock->expects($this->any())->method('scopeDate')->willReturn($dateTime);

        $this->dateTimeMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime')->getMock();

        $this->flagMock = $this->getMockBuilder('Magento\Reports\Model\Flag')
            ->disableOriginalConstructor()
            ->setMethods(['setReportFlagCode', 'unsetData', 'loadSelf', 'setFlagData', 'setLastUpdate', 'save'])
            ->getMock();

        $this->flagFactoryMock = $this->getMockBuilder('Magento\Reports\Model\FlagFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->flagFactoryMock->expects($this->any())->method('create')->willReturn($this->flagMock);

        $this->validatorMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\Timezone\Validator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->backendMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMock->expects($this->any())->method('getBackend')->willReturn($this->backendMock);

        $this->productMock = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->any())->method('getAttribute')->willReturn($this->attributeMock);

        $this->helperMock = $this->getMockBuilder('Magento\Reports\Model\Resource\Helper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewed = new Viewed(
            $this->contextMock,
            $this->loggerMock,
            $this->timezoneMock,
            $this->flagFactoryMock,
            $this->dateTimeMock,
            $this->validatorMock,
            $this->productMock,
            $this->helperMock
        );
    }

    /**
     * @param mixed $from
     * @param mixed $to
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $truncateCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $deleteCount
     * @dataProvider intervalsDataProvider
     * @return void
     */
    public function testAggregate($from, $to, $truncateCount, $deleteCount)
    {
        $this->connectionMock->expects($truncateCount)->method('truncateTable');
        $this->connectionMock->expects($deleteCount)->method('delete');

        $this->helperMock
            ->expects($this->at(0))
            ->method('updateReportRatingPos')
            ->with(
                $this->connectionMock,
                'day',
                'views_num',
                'report_viewed_product_aggregated_daily',
                'report_viewed_product_aggregated_daily'
            )
            ->willReturnSelf();
        $this->helperMock
            ->expects($this->at(1))
            ->method('updateReportRatingPos')
            ->with(
                $this->connectionMock,
                'month',
                'views_num',
                'report_viewed_product_aggregated_daily',
                'report_viewed_product_aggregated_monthly'
            )
            ->willReturnSelf();
        $this->helperMock
            ->expects($this->at(2))
            ->method('updateReportRatingPos')
            ->with(
                $this->connectionMock,
                'year',
                'views_num',
                'report_viewed_product_aggregated_daily',
                'report_viewed_product_aggregated_yearly'
            )
            ->willReturnSelf();

        $this->flagMock->expects($this->once())->method('unsetData')->willReturnSelf();
        $this->flagMock->expects($this->once())->method('loadSelf')->willReturnSelf();
        $this->flagMock->expects($this->never())->method('setFlagData')->willReturnSelf();
        $this->flagMock->expects($this->once())->method('setLastUpdate')->willReturnSelf();
        $this->flagMock->expects($this->once())->method('save')->willReturnSelf();
        $this->flagMock
            ->expects($this->once())
            ->method('setReportFlagCode')
            ->with(\Magento\Reports\Model\Flag::REPORT_PRODUCT_VIEWED_FLAG_CODE)
            ->willReturnSelf();

        $this->viewed->aggregate($from, $to);
    }

    /**
     * @return array
     */
    public function intervalsDataProvider()
    {
        return [
            [
                'from' => new \DateTime('+3 day'),
                'to' => new \DateTime('-3 day'),
                'truncateCount' => $this->never(),
                'deleteCount' => $this->once()
            ],
            [
                'from' => null,
                'to' => null,
                'truncateCount' => $this->once(),
                'deleteCount' => $this->never()
            ]
        ];
    }
}
