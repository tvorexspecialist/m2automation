<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for \Magento\CatalogSearch\Model\Search\IndexBuilder
 */
class IndexBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\Search\Adapter\Mysql\ConditionManager|MockObject */
    private $conditionManager;
    /** @var  \Magento\Search\Model\IndexScopeResolver|MockObject */
    private $scopeResolver;
    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|MockObject */
    private $adapter;

    /** @var \Magento\Framework\DB\Select|MockObject */
    private $select;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|MockObject */
    private $config;

    /** @var \Magento\Store\Model\StoreManagerInterface|MockObject */
    private $storeManager;

    /** @var \Magento\Framework\Search\RequestInterface|MockObject */
    private $request;

    /** @var \Magento\Search\Model\IndexScopeResolver|MockObject */
    private $resource;

    /**
     * @var \Magento\CatalogSearch\Model\Search\IndexBuilder
     */
    private $target;

    protected function setUp()
    {
        $this->select = $this->getMockBuilder('\Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->setMethods(['from', 'joinLeft', 'where', 'joinInner'])
            ->getMock();

        $this->adapter = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['select', 'quoteInto'])
            ->getMockForAbstractClass();
        $this->adapter->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->select));

        $this->resource = $this->getMockBuilder('\Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->with(\Magento\Framework\App\Resource::DEFAULT_READ_RESOURCE)
            ->will($this->returnValue($this->adapter));

        $this->request = $this->getMockBuilder('\Magento\Framework\Search\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getIndex', 'getDimensions'])
            ->getMockForAbstractClass();

        $this->config = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isSetFlag'])
            ->getMockForAbstractClass();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')->getMock();

        $this->scopeResolver = $this->getMockBuilder('\Magento\Search\Model\IndexScopeResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->conditionManager = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\ConditionManager')
            ->setMethods(['combineQueries', 'wrapBrackets', 'generateCondition'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->conditionManager->expects($this->any())
            ->method('combineQueries')
            ->willReturnCallback(
                function (array $queries, $expression) {
                    return implode(' ' . $expression . ' ', $queries);
                }
            );
        $this->conditionManager->expects($this->any())
            ->method('wrapBrackets')
            ->willReturnCallback(
                function ($expression) {
                    return '(' . $expression . ')';
                }
            );
        $this->conditionManager->expects($this->any())
            ->method('generateCondition')
            ->willReturnCallback(
                function ($left, $operator, $right) {
                    return $left . $operator . $right;
                }
            );

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            'Magento\CatalogSearch\Model\Search\IndexBuilder',
            [
                'resource' => $this->resource,
                'config' => $this->config,
                'storeManager' => $this->storeManager,
                'scopeResolver' => $this->scopeResolver,
                'conditionManager' => $this->conditionManager,
            ]
        );
    }

    public function testBuildWithOutOfStock()
    {
        $tableSuffix = 'index_default';
        $index = 'test_name_of_index';

        $this->mockBuild($index, $tableSuffix);

        $this->config->expects($this->once())
            ->method('isSetFlag')
            ->with('cataloginventory/options/show_out_of_stock')
            ->will($this->returnValue(true));

        $this->request->expects($this->exactly(2))
            ->method('getDimensions')
            ->willReturn([]);

        $result = $this->target->build($this->request);
        $this->assertSame($this->select, $result);
    }

    public function testBuildWithoutOutOfStock()
    {
        $scopeId = '113';
        $tableSuffix = 'index_113';
        $index = 'test_index_name';

        $dimensions = [
            $this->createDimension('scope', $scopeId),
            $this->createDimension('someName', 'someValue'),
        ];

        $this->request->expects($this->exactly(2))
            ->method('getDimensions')
            ->willReturn($dimensions);

        $this->mockBuild($index, $tableSuffix);

        $this->config->expects($this->once())
            ->method('isSetFlag')
            ->with('cataloginventory/options/show_out_of_stock')
            ->will($this->returnValue(false));
        $this->adapter->expects($this->once())->method('quoteInto')
            ->with(' AND stock_index.website_id = ?', 1)->willReturn(' AND stock_index.website_id = 1');
        $website = $this->getMockBuilder('Magento\Store\Model\Website')->disableOriginalConstructor()->getMock();
        $website->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getWebsite')->willReturn($website);
        $this->select->expects($this->at(4))
            ->method('where')
            ->with('(someName=someValue)')
            ->willReturnSelf();
        $this->select->expects($this->at(5))
            ->method('joinLeft')
            ->with(
                ['stock_index' => 'cataloginventory_stock_status'],
                'search_index.entity_id = stock_index.product_id'
                . ' AND stock_index.website_id = 1',
                []
            )
            ->willReturnSelf();
        $this->select->expects($this->at(6))
            ->method('where')
            ->with('stock_index.stock_status = ?', 1)
            ->will($this->returnSelf());

        $result = $this->target->build($this->request);
        $this->assertSame($this->select, $result);
    }

    protected function mockBuild($index, $tableSuffix)
    {
        $this->request->expects($this->atLeastOnce())
            ->method('getIndex')
            ->will($this->returnValue($index));

        $this->resource->expects($this->any())
            ->method('getTableName')
            ->will(
                $this->returnCallback(
                    function ($index) {
                        return is_array($index) ? $index[0] . $index[1] : $index;
                    }
                )
            );

        $this->scopeResolver->expects($this->any())
            ->method('resolve')
            ->will(
                $this->returnCallback(
                    function ($index, $suffix) {
                        return $index . '_' . ($suffix ? 'index_' . $suffix : 'index_default');
                    }
                )
            );

        $this->select->expects($this->once())
            ->method('from')
            ->with(
                ['search_index' => $index . '_' . $tableSuffix],
                ['entity_id' => 'entity_id']
            )
            ->will($this->returnSelf());

        $this->select->expects($this->at(1))
            ->method('joinLeft')
            ->with(
                ['category_index' => 'catalog_category_product_index'],
                'search_index.entity_id = category_index.product_id',
                []
            )
            ->will($this->returnSelf());

        $this->select->expects($this->at(2))
            ->method('joinLeft')
            ->with(
                ['cea' => 'catalog_eav_attribute'],
                'search_index.attribute_id = cea.attribute_id',
                ['search_weight']
            )
            ->will($this->returnSelf());
        $this->select->expects($this->at(3))
            ->method('joinLeft')
            ->with(
                ['cpie' => $this->resource->getTableName('catalog_product_index_eav')],
                'search_index.entity_id = cpie.entity_id AND search_index.attribute_id = cpie.attribute_id',
                []
            )
            ->willReturnSelf();
    }

    /**
     * @param $name
     * @param $value
     * @return MockObject
     */
    private function createDimension($name, $value)
    {
        $dimension = $this->getMockBuilder('\Magento\Framework\Search\Request\Dimension')
            ->setMethods(['getName', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($value);
        return $dimension;
    }
}
