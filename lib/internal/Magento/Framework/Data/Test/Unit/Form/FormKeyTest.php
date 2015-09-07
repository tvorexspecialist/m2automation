<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Test\Unit\Form;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Math\Random;
use Magento\Framework\Session\SessionManager;

class FormKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mathRandomMock;

    /**
     * @var SessionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var FormKey
     */
    protected $formKey;

    protected function setUp()
    {
        $this->mathRandomMock = $this->getMock('Magento\Framework\Math\Random', [], [], '', false);
        $methods = ['setData', 'getData'];
        $this->sessionMock = $this->getMock('Magento\Framework\Session\SessionManager', $methods, [], '', false);
        $this->formKey = new FormKey(
            $this->mathRandomMock,
            $this->sessionMock
        );
    }

    public function testGetFormKeyNotExist()
    {
        $valueMap = [
            [FormKey::FORM_KEY, false, null],
            [FormKey::FORM_KEY, false, 'random_string'],
        ];
        $this->sessionMock
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValueMap($valueMap));
        $this->mathRandomMock
            ->expects($this->once())
            ->method('getRandomString')
            ->with(16)
            ->will($this->returnValue('random_string'));
        $this->sessionMock->expects($this->once())->method('setData')->with(FormKey::FORM_KEY, 'random_string');
        $this->formKey->getFormKey();
    }

    public function testGetFormKeyExists()
    {
        $this->sessionMock
            ->expects($this->exactly(2))
            ->method('getData')
            ->with(FormKey::FORM_KEY)
            ->will($this->returnValue('random_string'));
        $this->mathRandomMock
            ->expects($this->never())
            ->method('getRandomString');
        $this->sessionMock->expects($this->never())->method('setData');
        $this->assertEquals('random_string', $this->formKey->getFormKey());
    }

    public function testIsPresent()
    {
        $this->sessionMock->expects(static::once())
            ->method('getData')
            ->with(FormKey::FORM_KEY)
            ->willReturn('Form key');

        static::assertTrue($this->formKey->isPresent());
    }

    public function testSet()
    {
        $formKeyValue = 'Form key';

        $this->sessionMock->expects(static::once())
            ->method('setData')
            ->with(FormKey::FORM_KEY, $formKeyValue);

        $this->formKey->set($formKeyValue);
    }
}
