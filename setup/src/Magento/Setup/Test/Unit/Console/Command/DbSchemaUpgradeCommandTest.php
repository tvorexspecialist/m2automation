<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Module\ModuleList;
use Magento\Setup\Console\Command\DbSchemaUpgradeCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DbSchemaUpgradeCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Model\InstallerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $installerFactory;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deploymentConfig;

    protected function setup()
    {
        $this->installerFactory = $this->createMock(\Magento\Setup\Model\InstallerFactory::class);
        $this->deploymentConfig = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
    }

    /**
     * @dataProvider executeDataProvider
     * @param $options
     * @param $expectedOptions
     */
    public function testExecute($options, $expectedOptions)
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->will($this->returnValue(true));
        $installer = $this->createMock(\Magento\Setup\Model\Installer::class);
        $this->installerFactory->expects($this->once())->method('create')->will($this->returnValue($installer));
        $installer
            ->expects($this->once())
            ->method('installSchema')
            ->with($expectedOptions);

        $commandTester = new CommandTester(
            new DbSchemaUpgradeCommand($this->installerFactory, $this->deploymentConfig)
        );
        $commandTester->execute($options);
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                'options' => [
                    '--magento-init-params' => '',
                    '--convert_old_scripts' => false
                ],
                'expectedOptions' => [
                    'convert_old_scripts' => false,
                    'magento-init-params' => '',
                ]
            ],
        ];
    }

    public function testExecuteNoConfig()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->will($this->returnValue(false));
        $this->installerFactory->expects($this->never())->method('create');

        $commandTester = new CommandTester(
            new DbSchemaUpgradeCommand($this->installerFactory, $this->deploymentConfig)
        );
        $commandTester->execute([]);
        $this->assertStringMatchesFormat(
            'No information is available: the Magento application is not installed.%w',
            $commandTester->getDisplay()
        );
    }
}
