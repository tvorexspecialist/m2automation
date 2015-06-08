<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Backup\Factory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to backup code base and user data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackupCommand extends AbstractSetupCommand
{
    /**
     * Name of input options
     */

    const INPUT_KEY_CODE = 'code';
    const INPUT_KEY_MEDIA = 'media';
    const INPUT_KEY_DB = 'db';

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @var BackupRollbackFactory
     */
    private $backupRollbackFactory;

    /**
     * Existing deployment config
     */
    private $deploymentConfig;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param MaintenanceMode $maintenanceMode
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        MaintenanceMode $maintenanceMode,
        DeploymentConfig $deploymentConfig
    ) {
        $this->objectManager = $objectManagerProvider->get();
        $this->maintenanceMode = $maintenanceMode;
        $this->backupRollbackFactory = $this->objectManager->get('Magento\Framework\Setup\BackupRollbackFactory');
        $this->deploymentConfig = $deploymentConfig;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_CODE,
                null,
                InputOption::VALUE_NONE,
                'Take code and configuration files backup (excluding temporary files)'
            ),
            new InputOption(
                self::INPUT_KEY_MEDIA,
                null,
                InputOption::VALUE_NONE,
                'Take media backup'
            ),
            new InputOption(
                self::INPUT_KEY_DB,
                null,
                InputOption::VALUE_NONE,
                'Take complete database backup'
            ),
        ];
        $this->setName('setup:backup')
            ->setDescription('Takes backup of Magento Application code base, media and database')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()
            && ($input->getOption(self::INPUT_KEY_MEDIA) || $input->getOption(self::INPUT_KEY_DB))) {
            $output->writeln("<info>No information is available: the application is not installed.</info>");
            return;
        }
        try {
            $inputOptionProvided = false;
            $output->writeln('<info>Enabling maintenance mode</info>');
            $this->maintenanceMode->set(true);
            $time = time();
            if ($input->getOption(self::INPUT_KEY_CODE)) {
                $codeBackup = $this->backupRollbackFactory->create($output);
                $codeBackup->codeBackup($time);
                $inputOptionProvided = true;
            }
            if ($input->getOption(self::INPUT_KEY_MEDIA)) {
                $mediaBackup = $this->backupRollbackFactory->create($output);
                $mediaBackup->codeBackup($time, Factory::TYPE_MEDIA);
                $inputOptionProvided = true;
            }
            if ($input->getOption(self::INPUT_KEY_DB)) {
                $dbBackup = $this->backupRollbackFactory->create($output);
                $dbBackup->dbBackup($time);
                $inputOptionProvided = true;
            }
            if (!$inputOptionProvided) {
                throw new \InvalidArgumentException(
                    'No option is provided for the command to take backup.'
                );
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } finally {
            $output->writeln('<info>Disabling maintenance mode</info>');
            $this->maintenanceMode->set(false);
        }
    }
}
