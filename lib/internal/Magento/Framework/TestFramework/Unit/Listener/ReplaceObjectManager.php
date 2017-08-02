<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\TestFramework\Unit\Listener;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

/**
 * The event listener which instantiates ObjectManager before test run
 * @since 2.2.0
 */
class ReplaceObjectManager extends \PHPUnit_Framework_BaseTestListener
{
    /**
     * Replaces ObjectManager before run for each test
     *
     * Replace existing instance of the Application's ObjectManager with the mock.
     *
     * This avoids the issue with a not initialized ObjectManager
     * and makes working with ObjectManager predictable as it always contains clear mock for each test
     *
     * @param \PHPUnit_Framework_Test $test
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        if ($test instanceof \PHPUnit_Framework_TestCase) {
            $objectManagerMock = $test->getMockBuilder(ObjectManagerInterface::class)
                ->getMockForAbstractClass();
            $createMockCallback = function ($type) use ($test) {
                return $test->getMockBuilder($type)
                    ->disableOriginalConstructor()
                    ->getMockForAbstractClass();
            };
            $objectManagerMock->method('create')->willReturnCallback($createMockCallback);
            $objectManagerMock->method('get')->willReturnCallback($createMockCallback);
            ObjectManager::setInstance($objectManagerMock);
        }
    }
}
