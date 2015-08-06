<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Block\Adminhtml\Sales\Grid\Column\Renderer;

use Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date;

class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date
     */
    protected $date;

    /**
     * @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolverMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var string
     */
    private $globalStateLocaleBackup;

    /**
     * @param string $locale
     */
    private function mockGridDateRendererBehaviorWithLocale($locale)
    {
        $this->resolverMock->expects($this->any())->method('getLocale')->willReturn($locale);
        $this->localeDate->expects($this->any())->method('getDateFormat')->willReturnCallback(
            function ($value) use ($locale) {
                return (new \IntlDateFormatter(
                    $locale,
                    $value,
                    \IntlDateFormatter::NONE
                ))->getPattern();
            }
        );
    }

    /**
     * @param string $objectDataIndex
     * @param string $periodType
     */
    private function mockGridDateColumnConfig($objectDataIndex, $periodType)
    {
        $columnMock = $this->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')
            ->disableOriginalConstructor()
            ->setMethods(['getIndex', 'getPeriodType'])
            ->getMock();
        $columnMock->expects($this->once())->method('getIndex')->willReturn($objectDataIndex);
        $columnMock->expects($this->atLeastOnce())->method('getPeriodType')->willReturn($periodType);

        $this->date->setColumn($columnMock);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->localeDate = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDate
            ->expects($this->once())
            ->method('date')
            ->willReturnArgument(0);

        $this->contextMock = $this->getMockBuilder('Magento\Backend\Block\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock
            ->expects($this->once())
            ->method('getLocaleDate')
            ->willReturn($this->localeDate);

        $this->resolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->getMock();

        $this->date = new Date(
            $this->contextMock,
            $this->resolverMock
        );
        
        $this->globalStateLocaleBackup = \Locale::getDefault();
    }

    protected function tearDown()
    {
        $this->restoreTheDefaultLocaleGlobalState();
    }

    private function restoreTheDefaultLocaleGlobalState()
    {
        if (\Locale::getDefault() !== $this->globalStateLocaleBackup) {
            \Locale::setDefault($this->globalStateLocaleBackup);
        }
    }

    /**
     * @param string $data
     * @param string $locale
     * @param string $index
     * @param string $period
     * @param string $result
     * @dataProvider datesDataProvider
     * @return void
     */
    public function testRender($data, $locale, $index, $period, $result)
    {
        $this->mockGridDateRendererBehaviorWithLocale($locale);
        $this->mockGridDateColumnConfig($index, $period);

        $objectMock = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(['getData'])
            ->getMock();
        $objectMock->expects($this->once())->method('getData')->willReturn($data);


        $this->assertEquals($result, $this->date->render($objectMock));
    }

    /**
     * @return array
     */
    public function datesDataProvider()
    {
        return [
            [
                'data' => '2000',
                'locale' => 'en_US',
                'index' => 'period',
                'period' => 'year',
                'result' => '2000'
            ],
            [
                'data' => '2030',
                'locale' => 'en_US',
                'index' => 'period',
                'period' => 'year',
                'result' => '2030'
            ],
            [
                'data' => '2000-01',
                'locale' => 'en_US',
                'index' => 'period',
                'period' => 'month',
                'result' => '1/2000'
            ],
            [
                'data' => '2030-12',
                'locale' => 'en_US',
                'index' => 'period',
                'period' => 'month',
                'result' => '12/2030'
            ],
            [
                'data' => '2014-06-25',
                'locale' => 'en_US',
                'index' => 'period',
                'period' => 'day',
                'result' => 'Jun 25, 2014'
            ]
        ];
    }

    public function testDateIsRenderedIndependentOfSystemDefaultLocale()
    {
        \Locale::setDefault('de_DE');
        $this->mockGridDateRendererBehaviorWithLocale('en_US');
        $this->mockGridDateColumnConfig('period', 'day');

        $objectMock = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(['getData'])
            ->getMock();
        $objectMock->expects($this->any())->method('getData')->willReturn('2014-06-25');
        
        $this->assertEquals('Jun 25, 2014', $this->date->render($objectMock));
    }
}
