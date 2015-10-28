<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use \Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Product;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\Product
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected $productMock;

    /**
     * @var \Closure
     */
    protected $proceed;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var Product
     */
    protected $model;

    protected function setUp()
    {
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->subjectMock = $this->getMock('Magento\Catalog\Model\ResourceModel\Product', [], [], '', false);
        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Indexer\IndexerInterface',
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->indexerRegistryMock = $this->getMock(
            'Magento\Framework\Indexer\IndexerRegistry',
            ['get'],
            [],
            '',
            false
        );

        $this->proceed = function () {
            return $this->subjectMock;
        };

        $this->model = new Product($this->indexerRegistryMock);
    }

    public function testAfterSaveNonScheduled()
    {
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(false));
        $this->indexerMock->expects($this->once())->method('reindexRow')->with(1);
        $this->prepareIndexer();

        $this->productMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundSave($this->subjectMock, $this->proceed, $this->productMock)
        );
    }

    public function testAfterSaveScheduled()
    {
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(true));
        $this->indexerMock->expects($this->never())->method('reindexRow');
        $this->prepareIndexer();

        $this->productMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundSave($this->subjectMock, $this->proceed, $this->productMock)
        );
    }

    public function testAfterDeleteNonScheduled()
    {
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(false));
        $this->indexerMock->expects($this->once())->method('reindexRow')->with(1);
        $this->prepareIndexer();

        $this->productMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundDelete($this->subjectMock, $this->proceed, $this->productMock)
        );
    }

    public function testAfterDeleteScheduled()
    {
        $this->indexerMock->expects($this->once())->method('isScheduled')->will($this->returnValue(true));
        $this->indexerMock->expects($this->never())->method('reindexRow');
        $this->prepareIndexer();

        $this->productMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundDelete($this->subjectMock, $this->proceed, $this->productMock)
        );
    }

    protected function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }
}
