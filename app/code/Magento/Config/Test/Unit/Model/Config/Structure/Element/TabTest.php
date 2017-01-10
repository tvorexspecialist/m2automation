<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Element;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TabTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Tab
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_iteratorMock;

    protected function setUp()
    {
        $this->_iteratorMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\Iterator\Field::class,
            [],
            [],
            '',
            false
        );

        $this->_model = (new ObjectManager($this))->getObject(
            \Magento\Config\Model\Config\Structure\Element\Tab::class,
            ['childrenIterator' => $this->_iteratorMock]
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_iteratorMock);
    }

    public function testIsVisibleOnlyChecksPresenceOfChildren()
    {
        $this->_model->setData(['showInStore' => 0, 'showInWebsite' => 0, 'showInDefault' => 0], 'store');
        $this->_iteratorMock->expects($this->once())->method('current')->will($this->returnValue(true));
        $this->_iteratorMock->expects($this->once())->method('valid')->will($this->returnValue(true));
        $this->assertTrue($this->_model->isVisible());
    }
}
