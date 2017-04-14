<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\ConfigSet\EmulatedProcessorFacade;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Deploy\Model\DeploymentConfig\Validator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
    /**#@-*/

    /**
     * The emulated processor facade.
     *
     * @var EmulatedProcessorFacade
     */
    private $emulatedProcessorFacade;

    /**
     * The config change detector.
     *
     * @var ChangeDetector
     */
    private $changeDetector;

    /**
     * The hash manager.
     *
     * @var Hash
     */
    private $hash;

    /**
     * @param EmulatedProcessorFacade $emulatedProcessorFacade The emulated processor facade
     * @param ChangeDetector $changeDetector The config change detector
     * @param Hash $hash The hash manager
     */
    public function __construct(
        EmulatedProcessorFacade $emulatedProcessorFacade,
        ChangeDetector $changeDetector,
        Hash $hash
    ) {
        $this->emulatedProcessorFacade = $emulatedProcessorFacade;
        $this->changeDetector = $changeDetector;
        $this->hash = $hash;

        parent::__construct();
    }

    /**
     * @inheritdoc
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
                new InputArgument(static::ARG_VALUE, InputArgument::REQUIRED, 'Configuration value'),
                new InputOption(
                    static::OPTION_SCOPE,
                    null,
                    InputArgument::OPTIONAL,
                    'Configuration scope (default, website, or store)',
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                ),
                new InputOption(
                    static::OPTION_SCOPE_CODE,
                    null,
                    InputArgument::OPTIONAL,
                    'Scope code (required only if scope is not \'default\')'
                ),
                new InputOption(
                    static::OPTION_LOCK,
                    'l',
                    InputOption::VALUE_NONE,
                    'Lock value which prevents modification in the Admin'
                ),
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
        if ($this->changeDetector->hasChanges(System::CONFIG_TYPE)) {
            $output->writeln(
                '<error>'
                . 'This command is unavailable right now. '
                . 'To continue working with it please run app:config:import or setup:upgrade command before.'
                . '</error>'
            );

            return Cli::RETURN_FAILURE;
        }

        try {
            $message = $this->emulatedProcessorFacade->process(
                $input->getArgument(static::ARG_PATH),
                $input->getArgument(static::ARG_VALUE),
                $input->getOption(static::OPTION_SCOPE),
                $input->getOption(static::OPTION_SCOPE_CODE),
                $input->getOption(static::OPTION_LOCK)
            );

            $this->hash->regenerate(System::CONFIG_TYPE);

            $output->writeln('<info>' . $message . '</info>');

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Cli::RETURN_FAILURE;
        }
    }
}
