<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\ProductLink;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $hydratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkTypeProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkResourceMock;

    /**
     * @var \Magento\Catalog\Model\ProductLink\Repository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */

    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityCollectionProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkInitializerMock;

    /**
     * Test method
     */
    protected function setUp()
    {
        $linkManagementMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Management', [], [], '', false);
        $this->productRepositoryMock = $this->getMock('\Magento\Catalog\Model\ProductRepository', [], [], '', false);
        $this->entityCollectionProviderMock = $this->getMock(
            'Magento\Catalog\Model\ProductLink\CollectionProvider',
            [],
            [],
            '',
            false
        );
        $this->linkInitializerMock = $this->getMock(
            'Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks',
            [],
            [],
            '',
            false
        );
        $this->metadataPoolMock = $this->getMock(
            'Magento\Framework\Model\Entity\MetadataPool',
            [],
            [],
            '',
            false
        );
        $this->hydratorMock = $this->getMock(
            'Magento\Framework\Model\Entity\EntityHydrator',
            [],
            [],
            '',
            false
        );
        $this->metadataMock = $this->getMock(
            'Magento\Framework\Model\Entity\EntityMetadata',
            [],
            [],
            '',
            false
        );
        $this->linkTypeProvider = $this->getMock(
            'Magento\Catalog\Model\Product\LinkTypeProvider',
            [],
            [],
            '',
            false
        );
        $this->linkResourceMock = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Link',
            [],
            [],
            '',
            false
        );
        $this->metadataPoolMock->expects($this->any())->method('getHydrator')->willReturn($this->hydratorMock);
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($this->metadataMock);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\ProductLink\Repository',
            [
                'productRepository' => $this->productRepositoryMock,
                'entityCollectionProvider' => $this->entityCollectionProviderMock,
                'linkInitializer' => $this->linkInitializerMock,
                'linkManagement' => $linkManagementMock,
                'metadataPool' => $this->metadataPoolMock,
                'linkTypeProvider' => $this->linkTypeProvider,
                'linkResource' => $this->linkResourceMock
            ]
        );
    }

    /**
     * Test method
     */
    public function testSave()
    {
        $entityMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Link', [], [], '', false);
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);

        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, false, $productMock],
                ['linkedProduct', false, null, false, $linkedProductMock],
            ]
        ));
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getSku')->willReturn('product');
        $entityMock->expects($this->exactly(1))->method('getLinkType')->willReturn('linkType');
        $this->linkTypeProvider->expects($this->once())->method('getLinkTypes')->willReturn(['linkType' => $typeId]);
        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn('linkField');
        $this->hydratorMock->expects($this->once())->method('extract')
            ->with($productMock)
            ->willReturn(['linkField' => $parentId]);
        $this->linkResourceMock->expects($this->once())->method('saveProductLinks')->with($parentId, [
            $linkedProductId => ['product_id' => $linkedProductId]
        ], $typeId);
        $entityMock->expects($this->once())->method('__toArray')->willReturn([]);
        $linkedProductMock->expects($this->exactly(2))->method('getId')->willReturn($linkedProductId);

        $this->assertTrue($this->model->save($entityMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid data provided for linked products
     */
    public function testSaveWithException()
    {
        $entityMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Link', [], [], '', false);
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, false, $productMock],
                ['linkedProduct', false, null, false, $linkedProductMock],
            ]
        ));
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getSku')->willReturn('product');
        $entityMock->expects($this->exactly(1))->method('getLinkType')->willReturn('linkType');
        $this->linkTypeProvider->expects($this->once())->method('getLinkTypes')->willReturn(['linkType' => $typeId]);
        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn('linkField');
        $this->hydratorMock->expects($this->once())->method('extract')
            ->with($productMock)
            ->willReturn(['linkField' => $parentId]);
        $this->linkResourceMock->expects($this->once())->method('saveProductLinks')->with($parentId, [
            $linkedProductId => ['product_id' => $linkedProductId]
        ], $typeId)->willThrowException(new \Exception());
        $entityMock->expects($this->once())->method('__toArray')->willReturn([]);
        $linkedProductMock->expects($this->exactly(2))->method('getId')->willReturn($linkedProductId);
        $this->model->save($entityMock);
    }

    /**
     * Test method
     */
    public function testDelete()
    {
        $entityMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Link', [], [], '', false);
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $linkId = 33;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, false, $productMock],
                ['linkedProduct', false, null, false, $linkedProductMock],
            ]
        ));
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getSku')->willReturn('product');
        $entityMock->expects($this->exactly(1))->method('getLinkType')->willReturn('linkType');
        $this->linkTypeProvider->expects($this->once())->method('getLinkTypes')->willReturn(['linkType' => $typeId]);
        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn('linkField');
        $this->hydratorMock->expects($this->once())->method('extract')
            ->with($productMock)
            ->willReturn(['linkField' => $parentId]);
        $linkedProductMock->expects($this->once())->method('getId')->willReturn($linkedProductId);
        $this->linkResourceMock->expects($this->once())->method('getProductLinkId')
            ->with($parentId, $linkedProductId, $typeId)
            ->willReturn($linkId);
        $this->linkResourceMock->expects($this->once())->method('deleteProductLink')->with($linkId);

        $this->assertTrue($this->model->delete($entityMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid data provided for linked products
     */
    public function testDeleteWithInvalidDataException()
    {
        $entityMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Link', [], [], '', false);
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $linkId = 33;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, false, $productMock],
                ['linkedProduct', false, null, false, $linkedProductMock],
            ]
        ));
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getSku')->willReturn('product');
        $entityMock->expects($this->exactly(1))->method('getLinkType')->willReturn('linkType');
        $this->linkTypeProvider->expects($this->once())->method('getLinkTypes')->willReturn(['linkType' => $typeId]);
        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn('linkField');
        $this->hydratorMock->expects($this->once())->method('extract')
            ->with($productMock)
            ->willReturn(['linkField' => $parentId]);
        $linkedProductMock->expects($this->once())->method('getId')->willReturn($linkedProductId);
        $this->linkResourceMock->expects($this->once())->method('getProductLinkId')
            ->with($parentId, $linkedProductId, $typeId)
            ->willReturn($linkId);
        $this->linkResourceMock->expects($this->once())->method('deleteProductLink')
            ->with($linkId)
            ->willThrowException(new \Exception());
        $this->model->delete($entityMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Product with SKU linkedProduct is not linked to product with SKU product
     */
    public function testDeleteWithNoSuchEntityException()
    {
        $entityMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Link', [], [], '', false);
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, false, $productMock],
                ['linkedProduct', false, null, false, $linkedProductMock],
            ]
        ));
        $entityMock->expects($this->exactly(2))->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->exactly(2))->method('getSku')->willReturn('product');
        $entityMock->expects($this->once())->method('getLinkType')->willReturn('linkType');
        $this->model->delete($entityMock);
    }
}
