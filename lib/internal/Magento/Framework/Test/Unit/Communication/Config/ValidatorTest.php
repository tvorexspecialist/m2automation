<?php
namespace Magento\Framework\Test\Unit\Communication\Config;


use Magento\Framework\Communication\Config\Validator;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeProcessor;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    protected $typeProcessor;
    protected $methodsMap;

    public function setUp()
    {
        $this->methodsMap = $this->createMock(MethodsMap::class);

        $this->methodsMap->expects(static::any())
            ->method('getMethodsMap')
            ->will($this->throwException(new \InvalidArgumentException('message', 333)));


        $this->typeProcessor = $this->createMock(TypeProcessor::class);
        $this->typeProcessor->expects(static::any())
            ->method('isTypeSimple')
            ->willReturn(false);

        $this->typeProcessor->expects(static::any())
            ->method('isTypeSimple')
            ->willReturn(false);
    }

    /**
     * @expectedException  \LogicException
     * @expectedExceptionCode 333
     */
    public function testValidateResponseSchemaType()
    {
        /** @var Validator $validator */
        $validator = new Validator($this->typeProcessor, $this->methodsMap);
        $validator->validateResponseSchemaType('123', '123');
    }

    /**
     * @expectedException  \LogicException
     * @expectedExceptionCode 333
     */
    public function testValidateRequestSchemaType()
    {
        /** @var Validator $validator */
        $validator = new Validator($this->typeProcessor, $this->methodsMap);
        $validator->validateRequestSchemaType('123', '123');
    }
}