<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin\Store;

use \Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store\Group;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\ResourceModel\Group
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var Group
     */
    protected $model;

    protected function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Store\Model\ResourceModel\Group', [], [], '', false);
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
        $this->model = new Group($this->indexerRegistryMock);
    }

    /**
     * @param bool $isObjectNew
     * @param bool $websiteChanged
     * @param int $invalidateCounter
     * @return void
     * @dataProvider aroundSaveDataProvider
     */
    public function testAroundSave($isObjectNew, $websiteChanged, $invalidateCounter)
    {
        $groupMock = $this->getMock(
            'Magento\Store\Model\Group',
            ['dataHasChangedFor', 'isObjectNew', '__wakeup'],
            [],
            '',
            false
        );
        $groupMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('website_id')
            ->will($this->returnValue($websiteChanged));
        $groupMock->expects($this->once())->method('isObjectNew')->will($this->returnValue($isObjectNew));

        $closureMock = function (\Magento\Store\Model\Group $object) use ($groupMock) {
            $this->assertEquals($object, $groupMock);
            return $this->subjectMock;
        };

        $this->indexerMock->expects($this->exactly($invalidateCounter))->method('invalidate');
        $this->prepareIndexer($invalidateCounter);

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundSave($this->subjectMock, $closureMock, $groupMock)
        );
    }

    /**
     * @return array
     */
    public function aroundSaveDataProvider()
    {
        return [
            [false, false, 0],
            [false, true, 1],
            [true, false, 0],
            [true, true, 0],
        ];
    }

    /**
     * @return void
     */
    public function testAfterDelete()
    {
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->prepareIndexer(1);

        $this->assertEquals(
            $this->subjectMock,
            $this->model->afterDelete($this->subjectMock, $this->subjectMock)
        );
    }

    /**
     * @param int $invalidateCounter
     */
    protected function prepareIndexer($invalidateCounter)
    {
        $this->indexerRegistryMock->expects($this->exactly($invalidateCounter))
            ->method('get')
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }
}
