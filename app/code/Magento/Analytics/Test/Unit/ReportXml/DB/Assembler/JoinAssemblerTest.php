<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Tests\Unit\ReportXml\DB\Assembler;

/**
 * A unit test for testing of the 'join' assembler.
 */
class JoinAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Analytics\ReportXml\DB\Assembler\JoinAssembler
     */
    private $subject;

    /**
     * @var \Magento\Analytics\ReportXml\DB\NameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nameResolverMock;

    /**
     * @var \Magento\Analytics\ReportXml\DB\SelectBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectBuilderMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Analytics\ReportXml\DB\ColumnsResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $columnsResolverMock;

    /**
     * @var \Magento\Analytics\ReportXml\DB\ConditionResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionResolverMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->nameResolverMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\DB\NameResolver::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->selectBuilderMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\DB\SelectBuilder::class
        )
        ->disableOriginalConstructor()
        ->getMock();
        $this->selectBuilderMock->expects($this->any())
            ->method('getFilters')
            ->willReturn([]);
        $this->selectBuilderMock->expects($this->any())
            ->method('getColumns')
            ->willReturn([]);
        $this->selectBuilderMock->expects($this->any())
            ->method('getJoins')
            ->willReturn([]);

        $this->columnsResolverMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\DB\ColumnsResolver::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->conditionResolverMock = $this->getMockBuilder(
            \Magento\Analytics\ReportXml\DB\ConditionResolver::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $this->objectManagerHelper =
            new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            \Magento\Analytics\ReportXml\DB\Assembler\JoinAssembler::class,
            [
                'conditionResolver' => $this->conditionResolverMock,
                'nameResolver' => $this->nameResolverMock,
                'columnsResolver' => $this->columnsResolverMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testAssembleEmpty()
    {
        $queryConfigMock = [
            'source' => [
                'name' => 'sales_order',
                'alias' => 'sales'
            ]
        ];

        $this->selectBuilderMock->expects($this->never())
            ->method('setColumns');
        $this->selectBuilderMock->expects($this->never())
            ->method('setFilters');
        $this->selectBuilderMock->expects($this->never())
            ->method('setJoins');

        $this->assertEquals(
            $this->selectBuilderMock,
            $this->subject->assemble($this->selectBuilderMock, $queryConfigMock)
        );
    }

    /**
     * @return void
     */
    public function testAssembleNotEmpty()
    {
        $queryConfigMock = [
            'source' => [
                'name' => 'sales_order',
                'alias' => 'sales',
                'link-source' => [
                    [
                        'name' => 'sales_order_address',
                        'alias' => 'billing',
                        'link-type' => 'left',
                        'attribute' => [
                            [
                                'alias' => 'billing_address_id',
                                'name' => 'entity_id'
                            ]
                        ],
                        'using' => [
                            [
                                'glue' => 'and',
                                'condition' => [
                                    [
                                        'attribute' => 'parent_id',
                                        'operator' => 'eq',
                                        'type' => 'identifier',
                                        '_value' => 'entity_id'
                                    ]
                                ]
                            ]
                        ],
                        'filter' => [
                            [
                                'glue' => 'and',
                                'condition' => [
                                    [
                                        'attribute' => 'entity_id',
                                        'operator' => 'null'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $filtersMock = [];

        $joinsMock = [
            'billing' => [
                'link-type' => 'left',
                'table' => [
                    'billing' => 'sales_order_address'
                ],
                'condition' => '(billing.parent_id = `sales`.`entity_id`)'
            ]
        ];

        $this->nameResolverMock->expects($this->at(0))
            ->method('getAlias')
            ->with($queryConfigMock['source'])
            ->willReturn($queryConfigMock['source']['alias']);
        $this->nameResolverMock->expects($this->at(1))
            ->method('getAlias')
            ->with($queryConfigMock['source']['link-source'][0])
            ->willReturn($queryConfigMock['source']['link-source'][0]['alias']);
        $this->nameResolverMock->expects($this->any())
            ->method('getName')
            ->with($queryConfigMock['source']['link-source'][0])
            ->willReturn($queryConfigMock['source']['link-source'][0]['name']);

        $this->conditionResolverMock->expects($this->at(0))
            ->method('getFilter')
            ->with(
                $this->selectBuilderMock,
                $queryConfigMock['source']['link-source'][0]['using'],
                $queryConfigMock['source']['link-source'][0]['alias'],
                $queryConfigMock['source']['alias']
            )
            ->willReturn('(billing.parent_id = `sales`.`entity_id`)');

        if (isset($queryConfigMock['source']['link-source'][0]['filter'])) {
            $filtersMock = ['(sales.entity_id IS NULL)'];

            $this->conditionResolverMock->expects($this->at(1))
                ->method('getFilter')
                ->with(
                    $this->selectBuilderMock,
                    $queryConfigMock['source']['link-source'][0]['filter'],
                    $queryConfigMock['source']['link-source'][0]['alias'],
                    $queryConfigMock['source']['alias']
                )
                ->willReturn($filtersMock[0]);

            $this->columnsResolverMock->expects($this->once())
                ->method('getColumns')
                ->with($this->selectBuilderMock, $queryConfigMock['source']['link-source'][0])
                ->willReturn(
                    [
                        'entity_id' => 'sales.entity_id',
                        'billing_address_id' => 'billing.entity_id'
                    ]
                );

            $this->selectBuilderMock->expects($this->once())
                ->method('setColumns')
                ->with(
                    [
                        'entity_id' => 'sales.entity_id',
                        'billing_address_id' => 'billing.entity_id'
                    ]
                );
        }

        $this->selectBuilderMock->expects($this->once())
            ->method('setFilters')
            ->with($filtersMock);
        $this->selectBuilderMock->expects($this->once())
            ->method('setJoins')
            ->with($joinsMock);

        $this->assertEquals(
            $this->selectBuilderMock,
            $this->subject->assemble($this->selectBuilderMock, $queryConfigMock)
        );
    }
}
