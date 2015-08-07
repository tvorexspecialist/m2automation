<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobFactory;

class JobFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var JobFactory
     */
    private $jobFactory;

    public function setUp()
    {
        $serviceManager = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', [], '', false);
        $jobDbRollback = $this->getMock('Magento\Setup\Model\Cron\JobDbRollback', [], [], '', false);
        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $status->expects($this->once())->method('getStatusFilePath')->willReturn('path_a');
        $status->expects($this->once())->method('getLogFilePath')->willReturn('path_b');
        $maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $objectManagerProvider->expects($this->atLeastOnce())->method('get')->willReturn($this->objectManager);

        $upgradeCommand = $this->getMock('Magento\Setup\Console\Command\UpgradeCommand', [], [], '', false);
        $componentUninstallerFactory = $this->getMock(
            'Magento\Setup\Model\Cron\ComponentUninstallerFactory',
            [],
            [],
            '',
            false
        );

        $returnValueMap =[
            ['Magento\Setup\Model\Cron\Status', $status],
            ['Magento\Setup\Console\Command\UpgradeCommand', $upgradeCommand],
            ['Magento\Framework\App\MaintenanceMode', $maintenanceMode],
            ['Magento\Setup\Model\ObjectManagerProvider', $objectManagerProvider],
            ['Magento\Setup\Model\Cron\ComponentUninstallerFactory', $componentUninstallerFactory],
        ];

        $serviceManager->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnValueMap($returnValueMap));

        $this->jobFactory = new JobFactory($serviceManager);
    }

    public function testUpgrade()
    {
        $this->assertInstanceOf('Magento\Setup\Model\Cron\AbstractJob', $this->jobFactory->create('setup:upgrade', []));
    }

    public function testRollback()
    {
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Setup\BackupRollbackFactory')
            ->willReturn($this->getMock('Magento\Framework\Setup\BackupRollbackFactory', [], [], '', false));
        $this->assertInstanceOf(
            'Magento\Setup\Model\Cron\AbstractJob',
            $this->jobFactory->create('setup:rollback', [])
        );
    }

    public function testComponentUninstall()
    {
        $this->assertInstanceOf(
            'Magento\Setup\Model\Cron\JobComponentUninstall',
            $this->jobFactory->create('setup:component:uninstall', [])
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage job is not supported
     */
    public function testCreateUnknownJob()
    {
        $this->jobFactory->create('unknown', []);
    }
}

// functions to override native php functions
namespace Magento\Setup\Model\Cron;

function fopen()
{
    return 'filestream';
}

function is_resource()
{
    return true;
}

function get_resource_type()
{
    return 'stream';
}
