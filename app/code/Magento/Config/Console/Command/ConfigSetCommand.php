<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command;

use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Scope\ValidatorInterface;

/**
 * Command provides possibility to change system configuration.
 */
class ConfigSetCommand extends Command
{
    /**#@+
     * Constants for arguments and options.
     */
    const ARG_PATH = 'path';
    const ARG_VALUE = 'value';
    const OPTION_SCOPE = 'scope';
    const OPTION_SCOPE_CODE = 'scope-code';
    const OPTION_LOCK = 'lock';
    const OPTION_FORCE = 'force';
    /**#@-*/

    /**
     * The processor factory.
     *
     * @var ConfigSetProcessorFactory
     */
    private $configSetProcessorFactory;

    /**
     * The scope validator.
     *
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param ConfigSetProcessorFactory $configSetProcessorFactory The processor factory
     * @param ValidatorInterface $validator The scope validator
     */
    public function __construct(
        ConfigSetProcessorFactory $configSetProcessorFactory,
        ValidatorInterface $validator
    ) {
        $this->configSetProcessorFactory = $configSetProcessorFactory;
        $this->validator = $validator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('config:set')
            ->setDescription('Change system configuration')
            ->setDefinition([
                new InputArgument(
                    static::ARG_PATH,
                    InputArgument::REQUIRED,
                    'Configuration path in format group/section/field_name'
                ),
                new InputArgument(static::ARG_VALUE, InputArgument::REQUIRED, 'Value of configuration'),
                new InputOption(
                    static::OPTION_SCOPE,
                    null,
                    InputArgument::OPTIONAL,
                    'Scope of configuration (default, website, store, etc.)',
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                ),
                new InputOption(
                    static::OPTION_SCOPE_CODE,
                    null,
                    InputArgument::OPTIONAL,
                    'Scope code of configuration'
                ),
                new InputOption(
                    static::OPTION_LOCK,
                    'l',
                    InputOption::VALUE_NONE,
                    'Lock value to prevent it modification via admin configuration'
                ),
                new InputOption(
                    static::OPTION_FORCE,
                    'f',
                    InputOption::VALUE_NONE,
                    'Force command to ignore duplicate or lock errors'
                )
            ]);

        parent::configure();
    }

    /**
     * Creates and run appropriate processor, depending on input options.
     *
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->validator->isValid(
                $input->getOption(static::OPTION_SCOPE),
                $input->getOption(static::OPTION_SCOPE_CODE)
            );
        } catch (LocalizedException $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Cli::RETURN_FAILURE;
        }

        $processor = $input->getOption(static::OPTION_LOCK)
            ? $this->configSetProcessorFactory->create(ConfigSetProcessorFactory::TYPE_LOCK)
            : $this->configSetProcessorFactory->create(ConfigSetProcessorFactory::TYPE_DEFAULT);

        return $processor->process($input, $output);
    }
}
