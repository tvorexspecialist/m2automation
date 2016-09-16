<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Option;

use \Magento\Catalog\Model\Product\Option\Repository;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Repository
     */
    protected $optionRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionResourceMock;

    /**
     * @var ProductCustomOptionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock(
            \Magento\Catalog\Model\ProductRepository::class,
            [],
            [],
            '',
            false
        );
        $this->optionResourceMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Option::class,
            [],
            [],
            '',
            false
        );
        $this->converterMock = $this->getMock(
            \Magento\Catalog\Model\Product\Option\Converter::class,
            [],
            [],
            '',
            false
        );
        $this->optionMock = $this->getMock(\Magento\Catalog\Model\Product\Option::class, [], [], '', false);
        $this->productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $optionFactory = $this->getMock(
            \Magento\Catalog\Model\Product\OptionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $optionCollectionFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPool->expects($this->any())->method('getMetadata')->willReturn($metadata);

        $this->optionRepository = new Repository(
            $this->productRepositoryMock,
            $this->optionResourceMock,
            $this->converterMock
        );

        $this->setProperties(
            $this->optionRepository,
            [
                'optionFactory' => $optionFactory,
                'optionCollectionFactory' => $optionCollectionFactory,
                'metadataPool' => $metadataPool
            ]
        );
    }

    public function testGetList()
    {
        $productSku = 'simple_product';
        $expectedResult = ['Expected_option'];
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->optionRepository->getList($productSku));
    }

    public function testDelete()
    {
        $this->optionResourceMock->expects($this->once())->method('delete')->with($this->optionMock);
        $this->assertTrue($this->optionRepository->delete($this->optionMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetNonExistingOption()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, false)
            ->willReturn($this->productMock);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn(null);
        $this->optionRepository->get($productSku, $optionId);
    }

    public function testGet()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, false)
            ->willReturn($this->productMock);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)->willReturn($this->optionMock);
        $this->assertEquals($this->optionMock, $this->optionRepository->get($productSku, $optionId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDeleteByIdentifierNonExistingOption()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([$this->optionMock]);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn(null);
        $this->optionRepository->deleteByIdentifier($productSku, $optionId);
    }

    public function testDeleteByIdentifier()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([$optionId => $this->optionMock]);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn($this->optionMock);
        $this->optionResourceMock->expects($this->once())->method('delete')->with($this->optionMock);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->assertTrue($this->optionRepository->deleteByIdentifier($productSku, $optionId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testDeleteByIdentifierWhenCannotRemoveOption()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([$optionId => $this->optionMock]);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn($this->optionMock);
        $this->optionResourceMock->expects($this->once())->method('delete')->with($this->optionMock);
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->assertTrue($this->optionRepository->deleteByIdentifier($productSku, $optionId));
    }

    /**
     * @param $object
     * @param array $properties
     */
    private function setProperties($object, $properties = [])
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        foreach ($properties as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $reflectionProperty = $reflectionClass->getProperty($key);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $value);
            }
        }
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage ProductSku should be specified
     */
    public function testSaveCouldNotSaveException()
    {
        $this->optionMock->expects($this->once())->method('getProductSku')->willReturn(null);
        $this->optionRepository->save($this->optionMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSaveNoSuchEntityException()
    {
        $productSku = 'simple_product';
        $optionId = 1;
        $this->optionMock->expects($this->once())->method('getProductSku')->willReturn($productSku);
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->optionMock->expects($this->any())->method('getOptionId')->willReturn($optionId);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn(['some options']);
        $this->productMock->expects($this->once())->method('getOptionById')->with($optionId)->willReturn(null);
        $this->optionRepository->save($this->optionMock);
    }

    public function testSave()
    {
        $productId = 123;
        $productSku = 'simple_product';
        $optionId = 1;
        $this->optionMock->expects($this->once())->method('getProductSku')->willReturn($productSku);
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->optionMock->expects($this->any())->method('getOptionId')->willReturn($optionId);
        $resourceModelMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resourceModelMock->expects($this->once())
            ->method('load')
            ->with($this->productMock, $productId)
            ->willReturnSelf();
        $this->productMock->expects($this->once())->method('getId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('getResource')->willReturn($resourceModelMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([]);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn($this->optionMock);
        $this->optionMock->expects($this->once())->method('getData')->with('values')->willReturn([
            ['option_type_id' => 3],
            ['option_type_id' => 4],
            ['option_type_id' => 5],
        ]);
        $this->optionMock->expects($this->once())->method('getValues')->willReturn([]);
        $this->assertEquals($this->optionMock, $this->optionRepository->save($this->optionMock));
    }
}
