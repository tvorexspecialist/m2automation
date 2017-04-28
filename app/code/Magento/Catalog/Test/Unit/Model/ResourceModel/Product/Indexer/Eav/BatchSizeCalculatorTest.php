<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\BatchSizeManagement;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator;

class BatchSizeCalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function testEstimateBatchSize()
    {
        $indexerId = 'default';
        $batchManagerMock = $this->getMock(BatchSizeManagement::class, [], [], '', false);
        $batchSizes = [
            $indexerId => 2000,
        ];
        $batchManagers = [
            $indexerId => $batchManagerMock,
        ];
        $model = new BatchSizeCalculator(
            $batchSizes,
            $batchManagers
        );
        $connectionMock = $this->getMock(AdapterInterface::class);

        $batchManagerMock->expects($this->once())
            ->method('ensureBatchSize')
            ->with($connectionMock, $batchSizes[$indexerId]);
        $this->assertEquals($batchSizes[$indexerId], $model->estimateBatchSize($connectionMock, $indexerId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testEstimateBatchSizeThrowsExceptionIfIndexerIdIsNotRecognized()
    {
        $model = new BatchSizeCalculator(
            [],
            []
        );
        $connectionMock = $this->getMock(AdapterInterface::class);

        $model->estimateBatchSize($connectionMock, 'wrong_indexer_id');
    }
}
