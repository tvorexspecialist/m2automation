<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Model\AttributeManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ConfigFactory;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreFile
 */
class AttributeManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeManagement
     */
    private $attributeManagement;

    /**
     * @var AttributeSetRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $setRepositoryMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCollectionMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfigMock;

    /**
     * @var ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityTypeFactoryMock;

    /**
     * @var AttributeGroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupRepositoryMock;

    /**
     * @var AttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeResourceMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCollectionFactoryMock;

    protected function setUp()
    {
        $this->setRepositoryMock =
            $this->getMock(AttributeSetRepositoryInterface::class, [], [], '', false);
        $this->attributeCollectionMock =
            $this->getMock(Collection::class, [], [], '', false);
        $this->eavConfigMock =
            $this->getMock(Config::class, [], [], '', false);
        $this->entityTypeFactoryMock =
            $this->getMock(ConfigFactory::class, ['create', '__wakeup'], [], '', false);
        $this->groupRepositoryMock =
            $this->getMock(AttributeGroupRepositoryInterface::class, [], [], '', false);
        $this->attributeRepositoryMock =
            $this->getMock(AttributeRepositoryInterface::class, [], [], '', false);
        $this->attributeResourceMock =
            $this->getMock(Attribute::class, [], [], '', false);
        $this->attributeCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeManagement = new AttributeManagement(
            $this->setRepositoryMock,
            $this->attributeCollectionMock,
            $this->eavConfigMock,
            $this->entityTypeFactoryMock,
            $this->groupRepositoryMock,
            $this->attributeRepositoryMock,
            $this->attributeResourceMock,
            $this->attributeCollectionFactoryMock
        );
    }

    /**
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage AttributeSet with id "2" does not exist.
     */
    public function testAssignNoSuchEntityException()
    {
        $entityTypeCode = 1;
        $attributeSetId = 2;
        $attributeGroupId = 3;
        $attributeCode = 4;
        $sortOrder = 5;

        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->will($this->throwException(new \Magento\Framework\Exception\NoSuchEntityException()));

        $this->attributeManagement->assign($entityTypeCode, $attributeSetId, $attributeGroupId, $attributeCode, $sortOrder);
    }

    /**
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Wrong attribute set id provided
     */
    public function testAssignInputException()
    {
        $entityTypeCode = 1;
        $attributeSetId = 2;
        $attributeGroupId = 3;
        $attributeCode = 4;
        $sortOrder = 5;
        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class, [], [], '', false);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->getMock(\Magento\Eav\Model\Entity\Type::class, [], [], '', false);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn($entityTypeCode+1);

        $this->attributeManagement->assign($entityTypeCode, $attributeSetId, $attributeGroupId, $attributeCode, $sortOrder);
    }

    /**
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Attribute group does not belong to attribute set
     */
    public function testAssignInputExceptionGroupInSet()
    {
        $entityTypeCode = 1;
        $attributeSetId = 2;
        $attributeGroupId = 3;
        $attributeCode = 4;
        $sortOrder = 5;
        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class, [], [], '', false);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->getMock(\Magento\Eav\Model\Entity\Type::class, [], [], '', false);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn($entityTypeCode);

        $attributeGroup = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeGroupInterface::class)
            ->setMethods(['getAttributeSetId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->groupRepositoryMock->expects($this->once())->method('get')->willReturn($attributeGroup);
        $attributeGroup->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId + 1);

        $this->attributeManagement->assign($entityTypeCode, $attributeSetId, $attributeGroupId, $attributeCode, $sortOrder);
    }

    public function testAssign()
    {
        $entityTypeCode = 1;
        $attributeSetId = 2;
        $attributeGroupId = 3;
        $attributeCode = 4;
        $sortOrder = 5;
        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class, [], [], '', false);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->getMock(\Magento\Eav\Model\Entity\Type::class, [], [], '', false);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn($entityTypeCode);
        $attributeMock = $this->getMock(\Magento\Eav\Model\Attribute::class, [], [], '', false);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityTypeCode, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn(16);
        $this->attributeResourceMock->expects($this->once())->method('saveInSetIncluding')
            ->with(
                $attributeMock,
                16,
                $attributeSetId,
                $attributeGroupId,
                $sortOrder
            )
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with($attributeSetId)->willReturnSelf();
        $attributeMock->expects($this->once())->method('loadEntityAttributeIdBySet')->willReturnSelf();
        $attributeMock->expects($this->once())->method('getData')->with('entity_attribute_id')->willReturnSelf();

        $attributeGroup = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeGroupInterface::class)
            ->setMethods(['getAttributeSetId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->groupRepositoryMock->expects($this->once())->method('get')->willReturn($attributeGroup);
        $attributeGroup->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId);

        $this->assertEquals(
            $attributeMock,
            $this->attributeManagement->assign($entityTypeCode, $attributeSetId, $attributeGroupId, $attributeCode, $sortOrder)
        );
    }

    public function testUnassign()
    {
        $attributeSetId = 1;
        $attributeCode = 'code';

        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class, [], [], '', false);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->getMock(\Magento\Eav\Model\Entity\Type::class, [], [], '', false);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $attributeMock = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute::class,
            [
                'getEntityAttributeId',
                'setAttributeSetId',
                'loadEntityAttributeIdBySet',
                'getIsUserDefined',
                'deleteEntity',
                '__wakeup'
            ],
            [],
            '',
            false);
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn('entity type code');
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with('entity type code', $attributeCode)
            ->willReturn($attributeMock);
        $attributeSetMock->expects($this->once())->method('getAttributeSetId')->willReturn(33);
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with(33)->willReturnSelf();
        $attributeMock->expects($this->once())->method('loadEntityAttributeIdBySet')->willReturnSelf();
        $attributeMock->expects($this->once())->method('getEntityAttributeId')->willReturn(12);
        $attributeMock->expects($this->once())->method('getIsUserDefined')->willReturn(true);
        $attributeMock->expects($this->once())->method('deleteEntity')->willReturnSelf();

        $this->assertTrue($this->attributeManagement->unassign($attributeSetId, $attributeCode));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Attribute "code" not found in attribute set 1.
     */
    public function testUnassignInputException()
    {
        $attributeSetId = 1;
        $attributeCode = 'code';

        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class, [], [], '', false);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->getMock(\Magento\Eav\Model\Entity\Type::class, [], [], '', false);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $attributeMock = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute::class,
            [
                'getEntityAttributeId',
                'setAttributeSetId',
                'loadEntityAttributeIdBySet',
                'getIsUserDefined',
                'deleteEntity',
                '__wakeup'
            ],
            [],
            '',
            false);
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn('entity type code');
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with('entity type code', $attributeCode)
            ->willReturn($attributeMock);
        $attributeSetMock->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId);
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with($attributeSetId)->willReturnSelf();
        $attributeMock->expects($this->once())->method('loadEntityAttributeIdBySet')->willReturnSelf();
        $attributeMock->expects($this->once())->method('getEntityAttributeId')->willReturn(null);
        $attributeMock->expects($this->never())->method('getIsUserDefined');
        $attributeMock->expects($this->never())->method('deleteEntity');

        $this->attributeManagement->unassign($attributeSetId, $attributeCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Attribute set not found: 1
     */
    public function testUnassignWithWrongAttributeSet()
    {
        $attributeSetId = 1234567;
        $attributeCode = 'code';

        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willThrowException(new NoSuchEntityException(__('hello')));

        $this->attributeManagement->unassign($attributeSetId, $attributeCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage System attribute can not be deleted
     */
    public function testUnassignStateException()
    {
        $attributeSetId = 1;
        $attributeCode = 'code';

        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class, [], [], '', false);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->getMock(\Magento\Eav\Model\Entity\Type::class, [], [], '', false);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $attributeMock = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute::class,
            [
                'getEntityAttributeId',
                'setAttributeSetId',
                'loadEntityAttributeIdBySet',
                'getIsUserDefined',
                'deleteEntity',
                '__wakeup'
            ],
            [],
            '',
            false);
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn('entity type code');
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with('entity type code', $attributeCode)
            ->willReturn($attributeMock);
        $attributeSetMock->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId);
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with($attributeSetId)->willReturnSelf();
        $attributeMock->expects($this->once())->method('loadEntityAttributeIdBySet')->willReturnSelf();
        $attributeMock->expects($this->once())->method('getEntityAttributeId')->willReturn(12);
        $attributeMock->expects($this->once())->method('getIsUserDefined')->willReturn(null);
        $attributeMock->expects($this->never())->method('deleteEntity');

        $this->attributeManagement->unassign($attributeSetId, $attributeCode);
    }

    public function testGetAttributes()
    {
        $entityType = 'type';
        $attributeSetId = 148;

        $attributeCollectionFactoryMock = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $attributeCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->attributeCollectionMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty(
            $this->attributeManagement,
            'attributeCollectionFactory',
            $attributeCollectionFactoryMock
        );

        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class, [], [], '', false);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $entityTypeMock = $this->getMock(\Magento\Eav\Model\Entity\Type::class, [], [], '', false);
        $this->eavConfigMock->expects($this->once())
            ->method('getEntityType')
            ->with($entityType)
            ->willReturn($entityTypeMock);
        $entityTypeMock->expects($this->once())->method('getId')->willReturn(88);
        $attributeSetMock->expects($this->exactly(2))->method('getAttributeSetId')->willReturn(88);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(88);
        $this->attributeCollectionMock->expects($this->once())
            ->method('setAttributeSetFilter')
            ->with(88)
            ->willReturnSelf();
        $attributeMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute::class, [], [], '', false);
        $this->attributeCollectionMock->expects($this->once())->method('load')->willReturnSelf();
        $this->attributeCollectionMock->expects($this->once())->method('getItems')->willReturn([$attributeMock]);

        $this->assertEquals([$attributeMock], $this->attributeManagement->getAttributes($entityType, $attributeSetId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeSetId = 148
     */
    public function testGetAttributesNoSuchEntityException()
    {
        $entityType = 'type';
        $attributeSetId = 148;

        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class, [], [], '', false);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $entityTypeMock = $this->getMock(\Magento\Eav\Model\Entity\Type::class, [], [], '', false);
        $this->eavConfigMock->expects($this->once())
            ->method('getEntityType')
            ->with($entityType)
            ->willReturn($entityTypeMock);
        $entityTypeMock->expects($this->once())->method('getId')->willReturn(77);
        $attributeSetMock->expects($this->once())->method('getAttributeSetId')->willReturn(88);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(88);

        $this->attributeManagement->getAttributes($entityType, $attributeSetId);
    }
}
