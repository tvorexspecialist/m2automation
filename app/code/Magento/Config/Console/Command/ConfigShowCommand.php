<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\MetadataProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ScopePathResolver;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\Scope\ValidatorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\Console\Cli;

/**
 * Command provides possibility to show system configuration.
 */
class ConfigShowCommand extends Command
{
    /**#@+
     * Names of input arguments or options.
     */
    const INPUT_OPTION_SCOPE = 'scope';
    const INPUT_OPTION_SCOPE_CODE = 'scope-code';
    const INPUT_ARGUMENT_PATH = 'path';
    /**#@-*/

    /**
     * @var ValidatorInterface
     */
    private $scopeValidator;

    /**
     * @var ConfigSourceInterface
     */
    private $configSource;

    /**
     * @var ScopePathResolver
     */
    private $pathResolver;

    /**
     * @var MetadataProcessor
     */
    private $metadataProcessor;

    /**
     * @param ValidatorInterface $scopeValidator
     * @param ConfigSourceInterface $configSource
     * @param ScopePathResolver $pathResolver
     * @param MetadataProcessor $metadataProcessor
     * @internal param ScopeConfigInterface $appConfig
     */
    public function __construct(
        ValidatorInterface $scopeValidator,
        ConfigSourceInterface $configSource,
        ScopePathResolver $pathResolver,
        MetadataProcessor $metadataProcessor
    ) {
        parent::__construct();
        $this->scopeValidator = $scopeValidator;
        $this->configSource = $configSource;
        $this->pathResolver = $pathResolver;
        $this->metadataProcessor = $metadataProcessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument(
            self::INPUT_ARGUMENT_PATH,
            InputArgument::OPTIONAL,
            'Configuration path for example group/section/field_name'
        );
        $this->addOption(
            self::INPUT_OPTION_SCOPE,
            null,
            InputOption::VALUE_OPTIONAL,
            'Scope for configuration, if not set use \'default\'',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $this->addOption(
            self::INPUT_OPTION_SCOPE_CODE,
            null,
            InputOption::VALUE_OPTIONAL,
            'Scope code for configuration, empty string by default',
            ''
        );
        $this->setName('config:show')
            ->setDescription('Shows configuration value for given path');
        parent::configure();
    }

    /**
     * Displays configuration value for given configuration path.
     *
     * Shows error message if configuration for given path doesn't exists
     * or scope/scope-code doesn't pass validation.
     *
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scope = $input->getOption(self::INPUT_OPTION_SCOPE);
        $scopeCode = $input->getOption(self::INPUT_OPTION_SCOPE_CODE);
        $inputPath = $input->getArgument(self::INPUT_ARGUMENT_PATH);

        try {
            $this->scopeValidator->isValid($scope, $scopeCode);
            $configPath = $this->pathResolver->resolve($inputPath, $scope, $scopeCode);
            $configValue = $this->configSource->get($configPath);
        } catch (LocalizedException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return Cli::RETURN_FAILURE;
        }

        if ($configValue === null) {
            $output->writeln(sprintf(
                '<error>%s</error>',
                __('Configuration for path: "%1" doesn\'t exist', $inputPath)->render()
            ));
            return Cli::RETURN_FAILURE;
        }

        $this->outputResult($output, $configValue, $inputPath);
        return Cli::RETURN_SUCCESS;
    }

    /**
     * Output single configuration value or list of values if array given.
     *
     * @param OutputInterface $output An OutputInterface instance
     * @param mixed $configValue single value or array of values
     * @param $configPath base configuration path
     * @param int $level depth level for nested configuration
     * @return void
     */
    private function outputResult(OutputInterface $output, $configValue, $configPath, $level = 0)
    {
        $margin = str_repeat(" ", max($level - 1, 0) * 2);
        if (is_string($configValue)) {
            $value = $this->metadataProcessor->processValue($configValue, $configPath);
            $output->writeln(sprintf("%s%s - %s", $margin, $configPath, $value));
        } else if (is_array($configValue)) {
            if ($level > 0) {
                $output->writeln(sprintf("%s%s:", $margin, $configPath ?: 'config'));
            }
            foreach ($configValue as $name => $value) {
                $childPath = empty($configPath) ? $name : ($configPath . '/' . $name);
                $this->outputResult($output, $value, $childPath, $level + 1);
            }
        }
    }
}
