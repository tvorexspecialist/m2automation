<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Framework\DB\Select;
use Magento\CatalogUrlRewrite\Model\Map\DataMapPoolInterface;
use Magento\CatalogUrlRewrite\Model\Map\DataProductMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUsedInProductsMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteMap;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\TemporaryTableService;

/**
 * Class DataCategoryUrlRewriteMapTest
 */
class DataCategoryUrlRewriteMapTest extends \PHPUnit_Framework_TestCase
{
    /** @var DataMapPoolInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $dataMapPoolMock;

    /** @var DataCategoryMap|\PHPUnit_Framework_MockObject_MockObject */
    private $dataCategoryMapMock;

    /** @var DataCategoryUsedInProductsMap|\PHPUnit_Framework_MockObject_MockObject */
    private $dataCategoryUsedInProductsMapMock;

    /** @var TemporaryTableService|\PHPUnit_Framework_MockObject_MockObject */
    private $temporaryTableServiceMock;

    /** @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    private $connectionMock;

    /** @var DataCategoryUrlRewriteMap|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    protected function setUp()
    {
        $this->dataMapPoolMock = $this->getMock(DataMapPoolInterface::class);
        $this->dataCategoryMapMock = $this->getMock(DataProductMap::class, [], [], '', false);
        $this->dataCategoryUsedInProductsMapMock = $this->getMock(
            DataCategoryUsedInProductsMap::class,
            [],
            [],
            '',
            false
        );
        $this->temporaryTableServiceMock = $this->getMock(TemporaryTableService::class, [], [], '', false);
        $this->connectionMock = $this->getMock(ResourceConnection::class, [], [], '', false);

        $this->dataMapPoolMock->expects($this->any())
            ->method('getDataMap')
            ->willReturnOnConsecutiveCalls($this->dataCategoryUsedInProductsMapMock, $this->dataCategoryMapMock);

        $this->model = (new ObjectManager($this))->getObject(
            DataCategoryUrlRewriteMap::class,
            [
                'connection' => $this->connectionMock,
                'dataMapPool' => $this->dataMapPoolMock,
                'temporaryTableService' => $this->temporaryTableServiceMock,
                'mapData' => [],
            ]
        );
    }

    /**
     * Tests getAllData, getData and resetData functionality
     */
    public function testGetAllData()
    {
        $productStoreIds = [
            '1' => ['store_id' => 1, 'category_id' => 1],
            '2' => ['store_id' => 2, 'category_id' => 1],
            '3' => ['store_id' => 3, 'category_id' => 1],
            '4' => ['store_id' => 1, 'category_id' => 2],
            '5' => ['store_id' => 2, 'category_id' => 2],
        ];

        $connectionMock = $this->getMock(AdapterInterface::class);
        $selectMock = $this->getMock(Select::class, [], [], '', false);

        $this->connectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);
        $connectionMock->expects($this->any())
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls($productStoreIds, $productStoreIds[3]);
        $selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('joinInner')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $this->dataCategoryMapMock->expects($this->once())
            ->method('getAllData')
            ->willReturn([]);
        $this->dataCategoryUsedInProductsMapMock->expects($this->once())
            ->method('getAllData')
            ->willReturn([]);
        $this->temporaryTableServiceMock->expects($this->any())
            ->method('createFromSelect')
            ->withConsecutive(
                $selectMock,
                $connectionMock,
                [
                    'PRIMARY' => ['url_rewrite_id'],
                    'HASHKEY_ENTITY_STORE' => ['hash_key'],
                    'ENTITY_STORE' => ['entity_id', 'store_id']
                ]
            )
            ->willReturn('tempTableName');

        $this->assertEquals($productStoreIds, $this->model->getAllData(1));
        $this->assertEquals($productStoreIds[3], $this->model->getData(1, '3_1'));
    }
}
