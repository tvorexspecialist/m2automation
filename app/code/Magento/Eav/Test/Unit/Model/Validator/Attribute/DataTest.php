<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for \Magento\Eav\Model\Validator\Attribute\Data
 */
namespace Magento\Eav\Test\Unit\Model\Validator\Attribute;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing  \Magento\Eav\Model\Validator\Attribute\Data::isValid
     *
     * @dataProvider isValidDataProvider
     *
     * @param array $attributeData
     * @param array|bool $result
     * @param bool $expected
     * @param array $messages
     * @param array $data
     */
    public function testIsValid(
        $attributeData,
        $result,
        $expected,
        $messages,
        $data = ['attribute' => 'new_test']
    ) {
        $entity = $this->_getEntityMock();
        $attribute = $this->_getAttributeMock($attributeData);
        $attrDataFactory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            ['create'],
            [
                'objectManager' => $this->getMock('Magento\Framework\ObjectManagerInterface'),
                'string' => $this->getMock('Magento\Framework\Stdlib\StringUtils')
            ]
        );

        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $validator->setAttributes([$attribute])->setData($data);
        if ($attribute->getDataModel() || $attribute->getFrontendInput()) {
            $dataModel = $this->_getDataModelMock($result);
            $attrDataFactory->expects(
                $this->once()
            )->method(
                'create'
            )->with(
                $attribute,
                $entity
            )->will(
                $this->returnValue($dataModel)
            );
        }
        $this->assertEquals($expected, $validator->isValid($entity));
        $this->assertEquals($messages, $validator->getMessages());
    }

    /**
     * Data provider for testIsValid
     *
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            'is_valid' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text',
                ],
                'attributeReturns' => true,
                'isValid' => true,
                'messages' => [],
            ],
            'is_invalid' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text',
                ],
                'attributeReturns' => ['Error'],
                'isValid' => false,
                'messages' => ['attribute' => ['Error']],
            ],
            'no_data_models' => [
                'attributeData' => ['attribute_code' => 'attribute', 'frontend_input' => 'text'],
                'attributeReturns' => ['Error'],
                'isValid' => false,
                'messages' => ['attribute' => ['Error']],
            ],
            'no_data_models_no_frontend_input' => [
                'attributeData' => ['attribute_code' => 'attribute'],
                'attributeReturns' => ['Error'],
                'isValid' => true,
                'messages' => [],
            ],
            'no_data_for attribute' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text',
                ],
                'attributeReturns' => true,
                'isValid' => true,
                'messages' => [],
                'setData' => ['attribute2' => 'new_test'],
            ],
            'is_valid_data_from_entity' => [
                'attributeData' => [
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text',
                ],
                'attributeReturns' => true,
                'isValid' => true,
                'messages' => [],
                'setData' => [],
            ]
        ];
    }

    /**
     * Testing \Magento\Eav\Model\Validator\Attribute\Data::isValid
     *
     * In this test entity attributes are got from attribute collection.
     */
    public function testIsValidAttributesFromCollection()
    {
        /** @var \Magento\Eav\Model\Entity\AbstractEntity $resource */
        $resource = $this->getMockForAbstractClass('Magento\Eav\Model\Entity\AbstractEntity', [], '', false);
        $attribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text',
            ]
        );
        $collection = $this->getMockBuilder('Magento\Framework\DataObject')->setMethods(['getItems'])->getMock();
        $collection->expects($this->once())->method('getItems')->will($this->returnValue([$attribute]));
        $entityType = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(['getAttributeCollection'])
            ->getMock();
        $entityType->expects($this->once())->method('getAttributeCollection')->will($this->returnValue($collection));
        $entity = $this->_getEntityMock();
        $entity->expects($this->once())->method('getResource')->will($this->returnValue($resource));
        $entity->expects($this->once())->method('getEntityType')->will($this->returnValue($entityType));
        $dataModel = $this->_getDataModelMock(true);
        $attrDataFactory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            ['create'],
            [
                'objectManager' => $this->getMock('Magento\Framework\ObjectManagerInterface'),
                'string' => $this->getMock('Magento\Framework\Stdlib\StringUtils')
            ]
        );
        $attrDataFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $attribute,
            $entity
        )->will(
            $this->returnValue($dataModel)
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);

        $validator->setData(['attribute' => 'new_test_data']);
        $this->assertTrue($validator->isValid($entity));
    }

    /**
     * @dataProvider whiteBlackListProvider
     * @param callable $callback
     */
    public function testIsValidBlackListWhiteListChecks($callback)
    {
        $attribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text',
            ]
        );
        $secondAttribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute2',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text',
            ]
        );
        $data = ['attribute' => 'new_test_data', 'attribute2' => 'some data'];
        $entity = $this->_getEntityMock();
        $dataModel = $this->_getDataModelMock(true, $data['attribute']);
        $attrDataFactory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            ['create'],
            [
                'objectManager' => $this->getMock('Magento\Framework\ObjectManagerInterface'),
                'string' => $this->getMock('Magento\Framework\Stdlib\StringUtils')
            ]
        );
        $attrDataFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $attribute,
            $entity
        )->will(
            $this->returnValue($dataModel)
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $validator->setAttributes([$attribute, $secondAttribute])->setData($data);
        $callback($validator);
        $this->assertTrue($validator->isValid($entity));
    }

    /**
     * @return array
     */
    public function whiteBlackListProvider()
    {
        $whiteCallback = function ($validator) {
            $validator->setAttributesWhiteList(['attribute']);
        };

        $blackCallback = function ($validator) {
            $validator->setAttributesBlackList(['attribute2']);
        };
        return ['white_list' => [$whiteCallback], 'black_list' => [$blackCallback]];
    }

    public function testSetAttributesWhiteList()
    {
        $attributes = ['attr1', 'attr2', 'attr3'];
        $attrDataFactory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            [],
            [
                'objectManager' => $this->getMock('Magento\Framework\ObjectManagerInterface'),
                'string' => $this->getMock('Magento\Framework\Stdlib\StringUtils')
            ]
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $result = $validator->setAttributesWhiteList($attributes);
        $this->assertAttributeEquals($attributes, '_attributesWhiteList', $validator);
        $this->assertEquals($validator, $result);
    }

    public function testSetAttributesBlackList()
    {
        $attributes = ['attr1', 'attr2', 'attr3'];
        $attrDataFactory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            [],
            [
                'objectManager' => $this->getMock('Magento\Framework\ObjectManagerInterface'),
                'string' => $this->getMock('Magento\Framework\Stdlib\StringUtils')
            ]
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $result = $validator->setAttributesBlackList($attributes);
        $this->assertAttributeEquals($attributes, '_attributesBlackList', $validator);
        $this->assertEquals($validator, $result);
    }

    public function testAddErrorMessages()
    {
        $data = ['attribute1' => 'new_test', 'attribute2' => 'some data'];
        $entity = $this->_getEntityMock();
        $firstAttribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute1',
                'data_model' => $firstDataModel = $this->_getDataModelMock(['Error1']),
                'frontend_input' => 'text',
            ]
        );
        $secondAttribute = $this->_getAttributeMock(
            [
                'attribute_code' => 'attribute2',
                'data_model' => $secondDataModel = $this->_getDataModelMock(['Error2']),
                'frontend_input' => 'text',
            ]
        );
        $expectedMessages = ['attribute1' => ['Error1'], 'attribute2' => ['Error2']];
        $expectedDouble = ['attribute1' => ['Error1', 'Error1'], 'attribute2' => ['Error2', 'Error2']];
        $factory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            ['create'],
            [
                'objectManager' => $this->getMock('Magento\Framework\ObjectManagerInterface'),
                'string' => $this->getMock('Magento\Framework\Stdlib\StringUtils')
            ]
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($factory);
        $validator->setAttributes([$firstAttribute, $secondAttribute])->setData($data);

        $factory->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            $firstAttribute,
            $entity
        )->will(
            $this->returnValue($firstDataModel)
        );
        $factory->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            $secondAttribute,
            $entity
        )->will(
            $this->returnValue($secondDataModel)
        );
        $factory->expects(
            $this->at(2)
        )->method(
            'create'
        )->with(
            $firstAttribute,
            $entity
        )->will(
            $this->returnValue($firstDataModel)
        );
        $factory->expects(
            $this->at(3)
        )->method(
            'create'
        )->with(
            $secondAttribute,
            $entity
        )->will(
            $this->returnValue($secondDataModel)
        );

        $this->assertFalse($validator->isValid($entity));
        $this->assertEquals($expectedMessages, $validator->getMessages());
        $this->assertFalse($validator->isValid($entity));
        $this->assertEquals($expectedDouble, $validator->getMessages());
    }

    /**
     * @param array $attributeData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getAttributeMock($attributeData)
    {
        $attribute = $this->getMockBuilder(
            'Magento\Eav\Model\Attribute'
        )->setMethods(
            ['getAttributeCode', 'getDataModel', 'getFrontendInput', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        if (isset($attributeData['attribute_code'])) {
            $attribute->expects(
                $this->any()
            )->method(
                'getAttributeCode'
            )->will(
                $this->returnValue($attributeData['attribute_code'])
            );
        }
        if (isset($attributeData['data_model'])) {
            $attribute->expects(
                $this->any()
            )->method(
                'getDataModel'
            )->will(
                $this->returnValue($attributeData['data_model'])
            );
        }
        if (isset($attributeData['frontend_input'])) {
            $attribute->expects(
                $this->any()
            )->method(
                'getFrontendInput'
            )->will(
                $this->returnValue($attributeData['frontend_input'])
            );
        }
        return $attribute;
    }

    /**
     * @param boolean $returnValue
     * @param string|null $argument
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getDataModelMock($returnValue, $argument = null)
    {
        $dataModel = $this->getMockBuilder(
            'Magento\Eav\Model\Attribute\Data\AbstractData'
        )->disableOriginalConstructor()->setMethods(
            ['validateValue']
        )->getMockForAbstractClass();
        if ($argument) {
            $dataModel->expects(
                $this->once()
            )->method(
                'validateValue'
            )->with(
                $argument
            )->will(
                $this->returnValue($returnValue)
            );
        } else {
            $dataModel->expects($this->any())->method('validateValue')->will($this->returnValue($returnValue));
        }
        return $dataModel;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getEntityMock()
    {
        $entity = $this->getMockBuilder(
            'Magento\Framework\Model\AbstractModel'
        )->setMethods(
            ['getAttribute', 'getResource', 'getEntityType', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        return $entity;
    }
}
