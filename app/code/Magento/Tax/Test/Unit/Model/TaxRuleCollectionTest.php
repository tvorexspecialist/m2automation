<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

use \Magento\Tax\Model\TaxRuleCollection;
 
class TaxRuleCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxRuleCollection
     */
    protected $model;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sortOrderBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxRuleMock;
    
    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMock(
            \Magento\Framework\Data\Collection\EntityFactory::class,
            [],
            [],
            '',
            false
        );
        $this->filterBuilderMock = $this->getMock(\Magento\Framework\Api\FilterBuilder::class, [], [], '', false);
        $this->searchCriteriaBuilderMock =
            $this->getMock(\Magento\Framework\Api\SearchCriteriaBuilder::class, [], [], '', false);
        $this->sortOrderBuilderMock = $this->getMock(\Magento\Framework\Api\SortOrderBuilder::class, [], [], '', false);
        $this->ruleServiceMock = $this->getMock(\Magento\Tax\Api\TaxRuleRepositoryInterface::class, [], [], '', false);
        $this->searchCriteriaMock = $this->getMock(
            \Magento\Framework\Api\SearchCriteria::class,
            [],
            [],
            '',
            false
        );
        $this->searchResultsMock = $this->getMock(
            \Magento\Tax\Api\Data\TaxRateSearchResultsInterface::class,
            [],
            [],
            '',
            false
        );

        $this->taxRuleMock = $this->getMock(\Magento\Tax\Model\Calculation\Rule::class, [], [], '', false);
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->model = new TaxRuleCollection(
            $this->entityFactoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock,
            $this->sortOrderBuilderMock,
            $this->ruleServiceMock
        );
    }

    public function testLoadData()
    {
        $this->ruleServiceMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultsMock);

        $this->searchResultsMock->expects($this->once())->method('getTotalCount')->willReturn(568);
        $this->searchResultsMock->expects($this->once())->method('getItems')->willReturn([$this->taxRuleMock]);
        $this->taxRuleMock->expects($this->once())->method('getId')->willReturn(33);
        $this->taxRuleMock->expects($this->once())->method('getCode')->willReturn(44);
        $this->taxRuleMock->expects($this->once())->method('getPriority')->willReturn('some priority');
        $this->taxRuleMock->expects($this->once())->method('getPosition')->willReturn('position');
        $this->taxRuleMock->expects($this->once())->method('getCalculateSubtotal')->willReturn(null);
        $this->taxRuleMock->expects($this->once())->method('getCustomerTaxClassIds')->willReturn('Post Code');
        $this->taxRuleMock->expects($this->once())->method('getProductTaxClassIds')->willReturn([12]);
        $this->taxRuleMock->expects($this->once())->method('getTaxRateIds')->willReturn([66]);

        $this->model->loadData();
    }
}
