<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Eav\Model\Entity\Attribute\Config
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheId;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attribute;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityType;

    protected function setUp()
    {
        $this->_attribute = $this->getMock(\Magento\Eav\Model\Entity\Attribute::class, [], [], '', false);
        $this->_entityType = $this->getMock(\Magento\Eav\Model\Entity\Type::class, [], [], '', false);
        $this->_readerMock = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\Config\Reader::class,
            [],
            [],
            '',
            false
        );
        $this->_cacheMock = $this->getMock(\Magento\Framework\App\Cache\Type\Config::class, [], [], '', false);
        $this->_cacheId = 'eav_attributes';
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with($this->_cacheId)
            ->willReturn('');

        $serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);

        $serializerMock->method('unserialize')
            ->willReturn([]);
        $this->mockObjectManager(
            [\Magento\Framework\Serialize\SerializerInterface::class => $serializerMock]
        );
        $this->_model = new \Magento\Eav\Model\Entity\Attribute\Config(
            $this->_readerMock,
            $this->_cacheMock,
            $this->_cacheId
        );
    }

    protected function tearDown()
    {
        $reflectionProperty = new \ReflectionProperty(\Magento\Framework\App\ObjectManager::class, '_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null);
    }

    /**
     * Mock application object manager to return configured dependencies.
     *
     * @param array $dependencies
     * @return void
     */
    private function mockObjectManager($dependencies)
    {
        $dependencyMap = [];
        foreach ($dependencies as $type => $instance) {
            $dependencyMap[] = [$type, $instance];
        }
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($dependencyMap));
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
    }

    public function testGetLockedFieldsEmpty()
    {
        $this->_entityType->expects($this->once())->method('getEntityTypeCode')->will($this->returnValue('test_code'));
        $this->_attribute->expects(
            $this->once()
        )->method(
            'getEntityType'
        )->will(
            $this->returnValue($this->_entityType)
        );

        $this->_attribute->expects(
            $this->once()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('attribute_code')
        );
        $result = $this->_model->getLockedFields($this->_attribute);
        $this->assertEquals([], $result);
    }

    public function testGetLockedFields()
    {
        $this->_entityType->expects(
            $this->once()
        )->method(
            'getEntityTypeCode'
        )->will(
            $this->returnValue('test_code1/test_code2')
        );
        $this->_attribute->expects(
            $this->once()
        )->method(
            'getEntityType'
        )->will(
            $this->returnValue($this->_entityType)
        );

        $this->_attribute->expects($this->once())->method('getAttributeCode')->will($this->returnValue('test_code'));
        $data = [
            'test_code1' => [
                'test_code2' => ['attributes' => ['test_code' => ['test_code1' => 'test_code1']]],
            ],
        ];
        $this->_model->merge($data);
        $result = $this->_model->getLockedFields($this->_attribute);
        $this->assertEquals(['test_code1' => 'test_code1'], $result);
    }

    public function testGetEntityAttributesLockedFields()
    {
        $data = [
            'entity_code' => [
                'attributes' => [
                    'attribute_code' => [
                        'attribute_data' => ['locked' => 'locked_field', 'code' => 'code_test'],
                    ],
                ],
            ],
        ];
        $this->_model->merge($data);
        $result = $this->_model->getEntityAttributesLockedFields('entity_code');
        $this->assertEquals(['attribute_code' => ['code_test']], $result);
    }
}
