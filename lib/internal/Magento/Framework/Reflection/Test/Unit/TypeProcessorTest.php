<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreStart
namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Exception\SerializationException;
use Magento\Framework\Reflection\Test\Unit\Fixture\TSample;
use Zend\Code\Reflection\ClassReflection;

/**
 * Type processor Test
 */
class TypeProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    protected $_typeProcessor;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->_typeProcessor = new \Magento\Framework\Reflection\TypeProcessor();
    }

    /**
     * Test Retrieving of processed types data.
     */
    public function testGetTypesData()
    {
        $this->_typeProcessor->setTypeData('typeA', ['dataA']);
        $this->_typeProcessor->setTypeData('typeB', ['dataB']);
        $this->assertEquals(
            ['typeA' => ['dataA'], 'typeB' => ['dataB']],
            $this->_typeProcessor->getTypesData()
        );
    }

    /**
     * Test set of processed types data.
     */
    public function testSetTypesData()
    {
        $this->_typeProcessor->setTypeData('typeC', ['dataC']);
        $this->assertEquals(['typeC' => ['dataC']], $this->_typeProcessor->getTypesData());
        $typeData = ['typeA' => ['dataA'], 'typeB' => ['dataB']];
        $this->_typeProcessor->setTypesData($typeData);
        $this->assertEquals($typeData, $this->_typeProcessor->getTypesData());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "NonExistentType" data type isn't declared. Verify the type and try again.
     */
    public function testGetTypeDataInvalidArgumentException()
    {
        $this->_typeProcessor->getTypeData('NonExistentType');
    }

    /**
     * Test retrieval of data type details for the given type name.
     */
    public function testGetTypeData()
    {
        $this->_typeProcessor->setTypeData('typeA', ['dataA']);
        $this->assertEquals(['dataA'], $this->_typeProcessor->getTypeData('typeA'));
    }

    /**
     * Test data type details for the same type name set multiple times.
     */
    public function testSetTypeDataArrayMerge()
    {
        $this->_typeProcessor->setTypeData('typeA', ['dataA1']);
        $this->_typeProcessor->setTypeData('typeA', ['dataA2']);
        $this->_typeProcessor->setTypeData('typeA', ['dataA3']);
        $this->_typeProcessor->setTypeData('typeA', [null]);
        $this->assertEquals(['dataA1', 'dataA2', 'dataA3', null], $this->_typeProcessor->getTypeData('typeA'));
    }

    public function testNormalizeType()
    {
        $this->assertEquals('blah', $this->_typeProcessor->normalizeType('blah'));
        $this->assertEquals('string', $this->_typeProcessor->normalizeType('str'));
        $this->assertEquals('int', $this->_typeProcessor->normalizeType('integer'));
        $this->assertEquals('boolean', $this->_typeProcessor->normalizeType('bool'));
        $this->assertEquals('anyType', $this->_typeProcessor->normalizeType('mixed'));
    }

    public function testIsTypeSimple()
    {
        $this->assertTrue($this->_typeProcessor->isTypeSimple('string'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('string[]'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('int'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('float'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('double'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('boolean'));
        $this->assertFalse($this->_typeProcessor->isTypeSimple('blah'));
    }

    public function testIsTypeAny()
    {
        $this->assertTrue($this->_typeProcessor->isTypeAny('mixed'));
        $this->assertTrue($this->_typeProcessor->isTypeAny('mixed[]'));
        $this->assertFalse($this->_typeProcessor->isTypeAny('int'));
        $this->assertFalse($this->_typeProcessor->isTypeAny('int[]'));
    }

    public function testIsArrayType()
    {
        $this->assertFalse($this->_typeProcessor->isArrayType('string'));
        $this->assertTrue($this->_typeProcessor->isArrayType('string[]'));
    }

    public function testIsValidTypeDeclaration()
    {
        $this->assertTrue($this->_typeProcessor->isValidTypeDeclaration('Traversable')); // Interface
        $this->assertTrue($this->_typeProcessor->isValidTypeDeclaration('stdObj')); // Class
        $this->assertTrue($this->_typeProcessor->isValidTypeDeclaration('array'));
        $this->assertTrue($this->_typeProcessor->isValidTypeDeclaration('callable'));
        $this->assertTrue($this->_typeProcessor->isValidTypeDeclaration('self'));
        $this->assertTrue($this->_typeProcessor->isValidTypeDeclaration('self'));
        $this->assertFalse($this->_typeProcessor->isValidTypeDeclaration('string'));
        $this->assertFalse($this->_typeProcessor->isValidTypeDeclaration('string[]'));
        $this->assertFalse($this->_typeProcessor->isValidTypeDeclaration('int'));
        $this->assertFalse($this->_typeProcessor->isValidTypeDeclaration('float'));
        $this->assertFalse($this->_typeProcessor->isValidTypeDeclaration('double'));
        $this->assertFalse($this->_typeProcessor->isValidTypeDeclaration('boolean'));
        $this->assertFalse($this->_typeProcessor->isValidTypeDeclaration('[]'));
        $this->assertFalse($this->_typeProcessor->isValidTypeDeclaration('mixed[]'));
        $this->assertFalse($this->_typeProcessor->isValidTypeDeclaration('stdObj[]'));
        $this->assertFalse($this->_typeProcessor->isValidTypeDeclaration('Traversable[]'));
    }

    public function getArrayItemType()
    {
        $this->assertEquals('string', $this->_typeProcessor->getArrayItemType('str[]'));
        $this->assertEquals('string', $this->_typeProcessor->getArrayItemType('string[]'));
        $this->assertEquals('integer', $this->_typeProcessor->getArrayItemType('int[]'));
        $this->assertEquals('boolean', $this->_typeProcessor->getArrayItemType('bool[]'));
        $this->assertEquals('any', $this->_typeProcessor->getArrayItemType('mixed[]'));
    }

    public function testTranslateTypeName()
    {
        $this->assertEquals(
            'TestModule1V1EntityItem',
            $this->_typeProcessor->translateTypeName(\Magento\TestModule1\Service\V1\Entity\Item::class)
        );
        $this->assertEquals(
            'TestModule3V1EntityParameter[]',
            $this->_typeProcessor->translateTypeName('\Magento\TestModule3\Service\V1\Entity\Parameter[]')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "\Magento\TestModule3\V1\Parameter[]" parameter type is invalid. Verify the parameter and try again.
     */
    public function testTranslateTypeNameInvalidArgumentException()
    {
        $this->_typeProcessor->translateTypeName('\Magento\TestModule3\V1\Parameter[]');
    }

    public function testTranslateArrayTypeName()
    {
        $this->assertEquals('ArrayOfComplexType', $this->_typeProcessor->translateArrayTypeName('complexType'));
    }

    public function testProcessSimpleTypeIntToString()
    {
        $value = 1;
        $type = 'string';
        $this->assertSame('1', $this->_typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeStringToInt()
    {
        $value = '1';
        $type = 'int';
        $this->assertSame(1, $this->_typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeMixed()
    {
        $value = 1;
        $type = 'mixed';
        $this->assertSame(1, $this->_typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeIntArrayToStringArray()
    {
        $value = [1, 2, 3, 4, 5];
        $type = 'string[]';
        $this->assertSame(['1', '2', '3', '4', '5'], $this->_typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeStringArrayToIntArray()
    {
        $value = ['1', '2', '3', '4', '5'];
        $type = 'int[]';
        $this->assertSame([1, 2, 3, 4, 5], $this->_typeProcessor->processSimpleAndAnyType($value, $type));
    }

    /**
     * @dataProvider processSimpleTypeExceptionProvider
     */
    public function testProcessSimpleTypeException($value, $type)
    {
        $this->expectException(
            SerializationException::class,
            'The "'
            . $value . '" value\'s type is invalid. The "' . $type . '" type was expected. Verify and try again.'
        );
        $this->_typeProcessor->processSimpleAndAnyType($value, $type);
    }

    public static function processSimpleTypeExceptionProvider()
    {
        return [
            "int type, string value" => ['test', 'int'],
            "float type, string value" => ['test', 'float'],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\SerializationException
     * @expectedExceptionMessage The "integer" value's type is invalid. The "int[]" type was expected. Verify and try again.
     */
    public function testProcessSimpleTypeInvalidType()
    {
        $value = 1;
        $type = 'int[]';
        $this->_typeProcessor->processSimpleAndAnyType($value, $type);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp /@param annotation is incorrect for the parameter "name" \w+/
     */
    public function testGetParamType()
    {
        $class = new ClassReflection(\Magento\Framework\Reflection\Test\Unit\DataObject::class);
        $methodReflection = $class->getMethod('setName');
        $paramsReflection = $methodReflection->getParameters();
        $this->_typeProcessor->getParamType($paramsReflection[0]);
    }

    public function testGetParameterDescription()
    {
        $class = new ClassReflection(\Magento\Framework\Reflection\Test\Unit\DataObject::class);
        $methodReflection = $class->getMethod('setName');
        $paramsReflection = $methodReflection->getParameters();
        $this->assertEquals('Name of the attribute', $this->_typeProcessor->getParamDescription($paramsReflection[0]));
    }

    public function testGetOperationName()
    {
        $this->assertEquals("resNameMethodName", $this->_typeProcessor->getOperationName("resName", "methodName"));
    }

    /**
     * Checks a case when method has only `@inheritdoc` annotation.
     */
    public function testGetReturnTypeWithInheritDocBlock()
    {
        $expected = [
            'type' => 'string',
            'isRequired' => true,
            'description' => null,
            'parameterCount' => 0
        ];

        $classReflection = new ClassReflection(TSample::class);
        $methodReflection = $classReflection->getMethod('getPropertyName');

        self::assertEquals($expected, $this->_typeProcessor->getGetterReturnType($methodReflection));
    }

    /**
     * Checks a case when method and parent interface don't have `@return` annotation.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Getter return type must be specified using @return annotation. See Magento\Framework\Reflection\Test\Unit\Fixture\TSample::getName()
     */
    public function testGetReturnTypeWithoutReturnTag()
    {
        $classReflection = new ClassReflection(TSample::class);
        $methodReflection = $classReflection->getMethod('getName');
        $this->_typeProcessor->getGetterReturnType($methodReflection);
    }
}
