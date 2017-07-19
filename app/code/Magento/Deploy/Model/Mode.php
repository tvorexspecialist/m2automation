<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Magento\Deploy\App\Mode\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\State;
use Magento\Framework\Config\File\ConfigFilePool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacadeFactory;
use Magento\Config\Console\Command\EmulatedAdminhtmlAreaProcessor;

/**
 * A class to manage Magento modes
 *
 * @SuppressWarnings("PMD.CouplingBetweenObjects")
 * @SuppressWarnings("PMD.ExcessiveParameterList")
 */
class Mode
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * The factory for processor facade.
     *
     * @var ProcessorFacadeFactory
     */
    private $processorFacadeFactory;

    /**
     * Emulator adminhtml area for CLI command.
     *
     * @var EmulatedAdminhtmlAreaProcessor
     */
    private $emulatedAreaProcessor;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Writer $writer
     * @param Reader $reader
     * @param MaintenanceMode $maintenanceMode
     * @param Filesystem $filesystem
     * @param ConfigProvider $configProvider
     * @param ProcessorFacadeFactory $processorFacadeFactory
     * @param EmulatedAdminhtmlAreaProcessor $emulatedAreaProcessor
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        Writer $writer,
        Reader $reader,
        MaintenanceMode $maintenanceMode,
        Filesystem $filesystem,
        ConfigProvider $configProvider,
        ProcessorFacadeFactory $processorFacadeFactory,
        EmulatedAdminhtmlAreaProcessor $emulatedAreaProcessor
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->writer = $writer;
        $this->reader = $reader;
        $this->maintenanceMode = $maintenanceMode;
        $this->filesystem = $filesystem;
        $this->configProvider = $configProvider;
        $this->processorFacadeFactory = $processorFacadeFactory;
        $this->emulatedAreaProcessor = $emulatedAreaProcessor;
    }

    /**
     * Enable production mode
     *
     * @return void
     */
    public function enableProductionMode()
    {
        $this->enableMaintenanceMode($this->output);
        $this->filesystem->regenerateStatic($this->output);
        $this->setStoreMode(State::MODE_PRODUCTION);
        $this->disableMaintenanceMode($this->output);
    }

    /**
     * Only lock static resource locations and set store mode, without handling static content
     *
     * @return void
     */
    public function enableProductionModeMinimal()
    {
        $this->setStoreMode(State::MODE_PRODUCTION);
    }

    /**
     * Enable Developer mode
     *
     * @return void
     */
    public function enableDeveloperMode()
    {
        $this->filesystem->cleanupFilesystem(
            [
                DirectoryList::CACHE,
                DirectoryList::GENERATED_CODE,
                DirectoryList::GENERATED_METADATA,
                DirectoryList::TMP_MATERIALIZATION_DIR,
                DirectoryList::STATIC_VIEW,
            ]
        );
        $this->setStoreMode(State::MODE_DEVELOPER);
    }

    /**
     * Get current mode information
     *
     * @return string
     * @throws \Exception
     */
    public function getMode()
    {
        $env = $this->reader->load();
        return isset($env[State::PARAM_MODE]) ? $env[State::PARAM_MODE] : null;
    }

    /**
     * Store mode in env.php
     *
     * @param string $mode
     * @return void
     */
    protected function setStoreMode($mode)
    {
        $this->saveAppConfigs($mode);
        $data = [
            ConfigFilePool::APP_ENV => [
                State::PARAM_MODE => $mode
            ]
        ];
        $this->writer->saveConfig($data);
    }

    /**
     * Save application configs while switching mode
     *
     * @param string $mode
     * @return void
     */
    private function saveAppConfigs($mode)
    {
        $configs = $this->configProvider->getConfigs($this->getMode(), $mode);
        foreach ($configs as $path => $value) {
            $this->emulatedAreaProcessor->process(function () use ($path, $value) {
                $this->processorFacadeFactory->create()->process(
                    $path,
                    $value,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    null,
                    true
                );
            });
            $this->output->writeln('Config "' . $path . ' = ' . $value . '" has been saved.');
        }
    }

    /**
     * Enable maintenance mode
     *
     * @param OutputInterface $output
     * @return void
     */
    protected function enableMaintenanceMode(OutputInterface $output)
    {
        $this->maintenanceMode->set(true);
        $output->writeln('Enabled maintenance mode');
    }

    /**
     * Disable maintenance mode
     *
     * @param OutputInterface $output
     * @return void
     */
    protected function disableMaintenanceMode(OutputInterface $output)
    {
        $this->maintenanceMode->set(false);
        $output->writeln('Disabled maintenance mode');
    }
}
