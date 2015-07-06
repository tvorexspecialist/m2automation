<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Framework\Object;
use Magento\Paypal\Model\Payflowpro;
use Magento\Paypal\Model\Payflow\Service\Response\ValidatorInterface;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator;

/**
 * Class ResponseValidatorTest
 *
 * Test for class \Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator
 */
class ResponseValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResponseValidator
     */
    protected $responseValidator;

    /**
     * @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    protected function setUp()
    {
        $this->validatorMock = $this->getMockBuilder(
            'Magento\Paypal\Model\Payflow\Service\Response\ValidatorInterface'
        )
            ->setMethods(['validate'])
            ->getMockForAbstractClass();

        $this->responseValidator = new ResponseValidator([$this->validatorMock]);
    }

    /**
     * @param Object $response
     * @param int $exactlyCount
     *
     * @dataProvider dataProviderForTestValidate
     */
    public function testValidate(Object $response, $exactlyCount)
    {
        $this->validatorMock->expects($this->exactly($exactlyCount))
            ->method('validate')
            ->with($response);

        $this->responseValidator->validate($response);
    }

    /**
     * @return array
     */
    public function dataProviderForTestValidate()
    {
        return [
            [
                'response' => new Object(['result' => Payflowpro::RESPONSE_CODE_APPROVED]),
                'exactlyCount' => 1
            ],
            [
                'response' => new Object(['result' => Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER]),
                'exactlyCount' => 1
            ],
            [
                'response' => new Object(['result' => Payflowpro::RESPONSE_CODE_INVALID_AMOUNT]),
                'exactlyCount' => 0
            ]
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Transaction has been declined
     */
    public function testValidateFail()
    {
        $response = new Object(
            [
                'result' => Payflowpro::RESPONSE_CODE_APPROVED,
                'respmsg' => 'Test error msg',
            ]
        );

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($response)
            ->willReturn(false);

        $this->responseValidator->validate($response);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Transaction has been declined
     */
    public function testValidateUnknownCode()
    {
        $response = new Object(
            [
                'result' => 7777777777,
                'respmsg' => 'Test error msg',
            ]
        );

        $this->validatorMock->expects($this->never())
            ->method('validate')
            ->with($response)
            ->willReturn(false);

        $this->responseValidator->validate($response);
    }
}
