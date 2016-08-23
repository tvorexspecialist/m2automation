<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\DateTime;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Setup\Model\DateTime\TimeZoneProvider;
use Magento\Setup\Model\ObjectManagerProvider;

class TimeZoneProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $timeZone = $this->getMock(\Magento\Framework\Stdlib\DateTime\Timezone::class, [], [], '', false);
        $objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManager->expects($this->once())
            ->method('create')
            ->with(
                \Magento\Framework\Stdlib\DateTime\Timezone::class,
                ['scopeType' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT]
            )
            ->willReturn($timeZone);
        /** @var ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject $objectManagerProvider */
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->willReturn($objectManager);
        $object = new TimeZoneProvider($objectManagerProvider);
        $this->assertSame($timeZone, $object->get());
        // Assert that the provider always returns the same object
        $this->assertSame($timeZone, $object->get());
    }
}
