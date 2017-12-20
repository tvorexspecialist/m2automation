<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Magento\Setup\Model\Declaration\Schema\Request;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\InstallerFactory;
use Magento\Framework\Setup\ConsoleLogger;
use Symfony\Component\Console\Input\InputOption;
use Magento\Setup\Model\ConfigModel;

/**
 * Command to install Magento application
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallCommand extends AbstractSetupCommand
{
    /**
     * Parameter indicating command whether to cleanup database in the install routine
     */
    const INPUT_KEY_CLEANUP_DB = 'cleanup-database';

    /**
     * Parameter to specify an order_increment_prefix
     */
    const INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX = 'sales-order-increment-prefix';

    /**
     * Parameter indicating command whether to install Sample Data
     */
    const INPUT_KEY_USE_SAMPLE_DATA = 'use-sample-data';

    /**
     * List of comma-separated module names. That must be enabled during installation
     * Available magic param all.
     */
    const INPUT_KEY_ENABLE_MODULES = 'enable_modules';

    /**
     * List of comma-separated module names. That must be avoided during installation.
     * Avaiable magic param all.
     */
    const INPUT_KEY_DISABLE_MODULES = 'disable_modules';

    /**
     * This mode allows to install Magento in declarative way
     */
    const DECLARATION_MODE_KEY = 'declaration_mode';

    /**
     * Regex for sales_order_increment_prefix validation.
     */
    const SALES_ORDER_INCREMENT_PREFIX_RULE = '/^.{0,20}$/';

    /**
     * Installer service factory
     *
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * @var ConfigModel
     */
    protected $configModel;

    /**
     * @var InstallStoreConfigurationCommand
     */
    protected $userConfig;

    /**
     * @var AdminUserCreateCommand
     */
    protected $adminUser;

    /**
     * Constructor
     *
     * @param InstallerFactory $installerFactory
     * @param ConfigModel $configModel
     * @param InstallStoreConfigurationCommand $userConfig
     * @param AdminUserCreateCommand $adminUser
     */
    public function __construct(
        InstallerFactory $installerFactory,
        ConfigModel $configModel,
        InstallStoreConfigurationCommand $userConfig,
        AdminUserCreateCommand $adminUser
    ) {
        $this->installerFactory = $installerFactory;
        $this->configModel = $configModel;
        $this->userConfig = $userConfig;
        $this->adminUser = $adminUser;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $inputOptions = $this->configModel->getAvailableOptions();
        $inputOptions = array_merge($inputOptions, $this->userConfig->getOptionsList());
        $inputOptions = array_merge($inputOptions, $this->adminUser->getOptionsList());
        $inputOptions = array_merge($inputOptions, [
            new InputOption(
                self::INPUT_KEY_CLEANUP_DB,
                null,
                InputOption::VALUE_NONE,
                'Cleanup the database before installation'
            ),
            new InputOption(
                self::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX,
                null,
                InputOption::VALUE_REQUIRED,
                'Sales order number prefix'
            ),
            new InputOption(
                self::INPUT_KEY_USE_SAMPLE_DATA,
                null,
                InputOption::VALUE_NONE,
                'Use sample data'
            ),
            new InputOption(
                Request::DUMP_ENABLE_OPTIONS,
                null,
                InputOption::VALUE_REQUIRED,
                'Should removed columns be dumped or recovered columns data reverted'
            ),
            new InputOption(
                self::INPUT_KEY_ENABLE_MODULES,
                null,
                InputOption::VALUE_OPTIONAL,
                'List of comma-separated module names. That must be included during installation. 
                Avaiable magic param "all".'
            ),
            new InputOption(
                self::INPUT_KEY_DISABLE_MODULES,
                null,
                InputOption::VALUE_OPTIONAL,
                'List of comma-separated module names. That must be avoided during installation. 
                Avaiable magic param "all".'
            ),
            new InputOption(
                self::DECLARATION_MODE_KEY,
                null,
                InputOption::VALUE_OPTIONAL,
                'This mode allows to install Magento in declarative way'
            )
        ]);
        $this->setName('setup:install')
            ->setDescription('Installs the Magento application')
            ->setDefinition($inputOptions);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $installer = $this->installerFactory->create($consoleLogger);
        $installer->install($input->getOptions());

        $importConfigCommand = $this->getApplication()->find(ConfigImportCommand::COMMAND_NAME);
        $arrayInput = new ArrayInput([]);
        $arrayInput->setInteractive($input->isInteractive());
        $importConfigCommand->run($arrayInput, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $inputOptions = $input->getOptions();

        $configOptionsToValidate = [];
        foreach ($this->configModel->getAvailableOptions() as $option) {
            if (array_key_exists($option->getName(), $inputOptions)) {
                $configOptionsToValidate[$option->getName()] = $inputOptions[$option->getName()];
            }
        }
        $errors = $this->configModel->validate($configOptionsToValidate);
        $errors = array_merge($errors, $this->adminUser->validate($input));
        $errors = array_merge($errors, $this->validate($input));
        $errors = array_merge($errors, $this->userConfig->validate($input));

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $output->writeln("<error>$error</error>");
            }
            throw new \InvalidArgumentException('Parameter validation failed');
        }
    }

    /**
     * Validate sales_order_increment_prefix value
     *
     * It will save the value which discarding characters after 20th to the database so it should be
     * validated in advance.
     *
     * @param InputInterface $input
     * @return string[] Array of error messages
     */
    public function validate(InputInterface $input)
    {
        $errors = [];
        $value = $input->getOption(self::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX);
        if (preg_match(self::SALES_ORDER_INCREMENT_PREFIX_RULE, $value) != 1) {
            $errors[] = 'Validation failed, ' . self::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX
                . ' must be 20 characters or less';
        }
        return $errors;
    }
}
