<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface as FieldTypeConverterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class KeywordTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver\KeywordType
     */
    private $resolver;

    /**
     * @var FieldTypeConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fieldTypeConverter = $this->getMockBuilder(FieldTypeConverterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['convert'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);

        $this->resolver = $objectManager->getObject(
            \Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver\KeywordType::class,
            [
                'fieldTypeConverter' => $this->fieldTypeConverter,
            ]
        );
    }

    /**
     * @dataProvider getFieldTypeProvider
     * @param $isComplexType
     * @param $isSearchable
     * @param $isAlwaysIndexable
     * @param $isFilterable
     * @param $expected
     * @return void
     */
    public function testGetFieldType($isComplexType, $isSearchable, $isAlwaysIndexable, $isFilterable, $expected)
    {
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['isComplexType', 'isSearchable', 'isAlwaysIndexable', 'isFilterable'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('isComplexType')
            ->willReturn($isComplexType);
        $attributeMock->expects($this->any())
            ->method('isSearchable')
            ->willReturn($isSearchable);
        $attributeMock->expects($this->any())
            ->method('isAlwaysIndexable')
            ->willReturn($isAlwaysIndexable);
        $attributeMock->expects($this->any())
            ->method('isFilterable')
            ->willReturn($isFilterable);
        $this->fieldTypeConverter->expects($this->any())
            ->method('convert')
            ->willReturn('something');

        $this->assertEquals(
            $expected,
            $this->resolver->getFieldType($attributeMock)
        );
    }

    /**
     * @return array
     */
    public function getFieldTypeProvider()
    {
        return [
            [true, true, true, true, 'something'],
            [true, false, false, false, 'something'],
            [true, false, false, true, 'something'],
            [false, false, false, true, 'something'],
            [false, false, false, false, ''],
            [false, false, true, false, ''],
            [false, true, false, false, ''],
        ];
    }
}
