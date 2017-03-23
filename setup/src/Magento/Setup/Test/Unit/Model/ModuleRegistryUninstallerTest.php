<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Setup\Model\ModuleRegistryUninstaller;

class ModuleRegistryUninstallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig\Writer
     */
    private $writer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\ModuleList\Loader
     */
    private $loader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Module\DataSetup
     */
    private $dataSetup;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var ModuleRegistryUninstaller
     */
    private $moduleRegistryUninstaller;

    public function setUp()
    {
        $this->deploymentConfig = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $this->writer = $this->getMock(\Magento\Framework\App\DeploymentConfig\Writer::class, [], [], '', false);
        $this->loader = $this->getMock(\Magento\Framework\Module\ModuleList\Loader::class, [], [], '', false);
        $this->dataSetup = $this->getMock(\Magento\Setup\Module\DataSetup::class, [], [], '', false);
        $dataSetupFactory = $this->getMock(\Magento\Setup\Module\DataSetupFactory::class, [], [], '', false);
        $dataSetupFactory->expects($this->any())->method('create')->willReturn($this->dataSetup);
        $this->output = $this->getMock(\Symfony\Component\Console\Output\OutputInterface::class, [], [], '', false);
        $this->moduleRegistryUninstaller = new ModuleRegistryUninstaller(
            $dataSetupFactory,
            $this->deploymentConfig,
            $this->writer,
            $this->loader
        );
    }

    public function testRemoveModulesFromDb()
    {
        $this->output->expects($this->atLeastOnce())->method('writeln');
        $this->dataSetup->expects($this->atLeastOnce())->method('deleteTableRow');
        $this->moduleRegistryUninstaller->removeModulesFromDb($this->output, ['moduleA', 'moduleB']);
    }

    public function testRemoveModulesFromDeploymentConfig()
    {
        $this->output->expects($this->atLeastOnce())->method('writeln');
        $this->deploymentConfig->expects($this->once())
            ->method('getConfigData')
            ->willReturn(['moduleA' => 1, 'moduleB' => 1, 'moduleC' => 1, 'moduleD' => 1]);
        $this->loader->expects($this->once())->method('load')->willReturn(['moduleC' => [], 'moduleD' => []]);
        $this->writer->expects($this->once())
            ->method('saveConfig')
            ->with(
                [
                    ConfigFilePool::APP_CONFIG => [
                        ConfigOptionsListConstants::KEY_MODULES => ['moduleC' => 1, 'moduleD' => 1]
                    ]
                ]
            );
        $this->moduleRegistryUninstaller->removeModulesFromDeploymentConfig($this->output, ['moduleA', 'moduleB']);
    }
}
