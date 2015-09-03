<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module;

use Magento\Setup\Module\SetupFactory;

class SetupFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $objectManager->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\App\Resource')
            ->willReturn($this->getMock('Magento\Framework\App\Resource', [], [], '', false));
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $factory = new SetupFactory($objectManagerProvider);
        $this->assertInstanceOf('Magento\Setup\Module\Setup', $factory->create());
    }
}
