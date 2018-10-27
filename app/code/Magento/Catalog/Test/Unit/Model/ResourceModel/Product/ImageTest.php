<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Image;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;

class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var AdapterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var Generator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $generatorMock;

    /**
     * @var ResourceConnection | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceMock->method('getTableName')->willReturnArgument(0);
        $this->generatorMock = $this->createMock(Generator::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getVisibleImagesSelectMock(): \PHPUnit_Framework_MockObject_MockObject
    {
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects($this->once())
            ->method('distinct')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('from')
            ->with(
                ['images' => Gallery::GALLERY_TABLE],
                'value as filepath'
            )->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('disabled = 0')
            ->willReturnSelf();

        return $selectMock;
    }

    /**
     * @param int $imagesCount
     * @dataProvider dataProvider
     */
    public function testGetCountAllProductImages(int $imagesCount): void
    {
        $selectMock = $this->getVisibleImagesSelectMock();
        $selectMock->expects($this->exactly(2))
            ->method('reset')
            ->withConsecutive(
                ['columns'],
                ['distinct']
            )->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('columns')
            ->with(new \Zend_Db_Expr('count(distinct value)'))
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($selectMock)
            ->willReturn($imagesCount);

        $imageModel = $this->objectManager->getObject(
            Image::class,
            [
                'generator' => $this->generatorMock,
                'resourceConnection' => $this->resourceMock
            ]
        );

        $this->assertSame($imagesCount, $imageModel->getCountAllProductImages());
    }

    /**
     * @param int $imagesCount
     * @param int $batchSize
     * @dataProvider dataProvider
     */
    public function testGetAllProductImages(int $imagesCount, int $batchSize): void
    {
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->getVisibleImagesSelectMock());

        $batchCount = (int)ceil($imagesCount / $batchSize);
        $fetchResultsCallback = $this->getFetchResultCallbackForBatches($imagesCount, $batchSize);
        $this->connectionMock->expects($this->exactly($batchCount))
            ->method('fetchAll')
            ->will($this->returnCallback($fetchResultsCallback));

        /** @var Select | \PHPUnit_Framework_MockObject_MockObject $selectMock */
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->generatorMock->expects($this->once())
            ->method('generate')
            ->with(
                'value_id',
                $selectMock,
                $batchSize,
                \Magento\Framework\DB\Query\BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
            )->will($this->returnCallback($this->getBatchIteratorCallback($selectMock, $batchCount)));

        $imageModel = $this->objectManager->getObject(
            Image::class,
            [
                'generator' => $this->generatorMock,
                'resourceConnection' => $this->resourceMock,
                'batchSize' => $batchSize
            ]
        );

        $this->assertCount($imagesCount, $imageModel->getAllProductImages());
    }

    /**
     * @param int $imagesCount
     * @param int $batchSize
     * @return \Closure
     */
    protected function getFetchResultCallbackForBatches(int $imagesCount, int $batchSize): \Closure
    {
        $fetchResultsCallback = function () use (&$imagesCount, $batchSize) {
            $batchSize = ($imagesCount >= $batchSize) ? $batchSize : $imagesCount;
            $imagesCount -= $batchSize;

            $getFetchResults = function ($batchSize): array {
                $result = [];
                $count = $batchSize;
                while ($count) {
                    $count--;
                    $result[$count] = $count;
                }

                return $result;
            };

            return $getFetchResults($batchSize);
        };

        return $fetchResultsCallback;
    }

    /**
     * @param Select | \PHPUnit_Framework_MockObject_MockObject $selectMock
     * @param int $batchCount
     * @return \Closure
     */
    protected function getBatchIteratorCallback(
        \PHPUnit_Framework_MockObject_MockObject $selectMock,
        int $batchCount
    ): \Closure
    {
        $getBatchIteratorCallback = function () use ($batchCount, $selectMock): array {
            $result = [];
            $count = $batchCount;
            while ($count) {
                $count--;
                $result[$count] = $selectMock;
            }

            return $result;
        };

        return $getBatchIteratorCallback;
    }

    /**
     * Data Provider
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            [300, 300],
            [300, 100],
            [139, 100],
            [67, 10],
            [154, 47],
            [0, 100]
        ];
    }
}
