<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Deploy;

use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRenderer;
use Magento\Setup\Console\Command\InstallCommand;

/**
 * The purpose of this class is enable/disable module and upgrade commands execution.
 */
class CliCommand
{
    /**
     * @var \Magento\Framework\Shell
     */
    private $shell;

    /**
     * @var TestModuleManager
     */
    private $testEnv;

    /**
     * @var ParametersHolder
     */
    private $parametersHolder;

    /**
     * ShellCommand constructor.
     *
     * @param    TestModuleManager $testEnv
     * @internal param Shell $shell
     */
    public function __construct(
        \Magento\TestFramework\Deploy\TestModuleManager $testEnv
    ) {
        $this->shell = new Shell(new CommandRenderer());
        $this->testEnv = $testEnv;
        $this->parametersHolder = new ParametersHolder();
    }

    /**
     * Copy Test module files and execute enable module command.
     *
     * @param  string $moduleName
     * @return string
     */
    public function introduceModule($moduleName)
    {
        $this->testEnv->addModuleFiles($moduleName);
        return $this->enableModule($moduleName);
    }

    /**
     * Execute enable module command.
     *
     * @param  string $moduleName
     * @return string
     */
    public function enableModule($moduleName)
    {
        $initParams = $this->parametersHolder->getInitParams();
        $enableModuleCommand = 'php -f ' . BP . '/bin/magento module:enable ' . $moduleName
            . ' -n -vvv --magento-init-params=' . $initParams['magento-init-params'];
        return $this->shell->execute($enableModuleCommand);
    }

    /**
     * Execute upgrade magento command.
     *
     * @return string
     */
    public function upgrade()
    {
        $initParams = $this->parametersHolder->getInitParams();
        $enableModuleCommand = 'php -f ' . BP . '/bin/magento setup:upgrade -vvv -n --magento-init-params='
            . $initParams['magento-init-params'];
        return $this->shell->execute($enableModuleCommand);
    }

    /**
     * Execute disable module command.
     *
     * @param  string $moduleName
     * @return string
     */
    public function disableModule($moduleName)
    {
        $initParams = $this->parametersHolder->getInitParams();
        $disableModuleCommand = 'php -f ' . BP . '/bin/magento module:disable '. $moduleName
            . ' -vvv --magento-init-params=' . $initParams['magento-init-params'];
        return $this->shell->execute($disableModuleCommand);
    }

    /**
     * Split quote db configuration.
     *
     * @return void
     */
    public function splitQuote()
    {
        $initParams = $this->parametersHolder->getInitParams();
        $installParams = $this->toCliArguments(
            $this->parametersHolder->getDbData('checkout')
        );
        $command = 'php -f ' . BP . '/bin/magento setup:db-schema:split-quote ' .
            implode(" ", array_keys($installParams)) .
            ' -vvv --magento-init-params=' .
            $initParams['magento-init-params'];

        $this->shell->execute($command, array_values($installParams));
    }

    /**
     * Split sales db configuration.
     *
     * @return void
     */
    public function splitSales()
    {
        $initParams = $this->parametersHolder->getInitParams();
        $installParams = $this->toCliArguments(
            $this->parametersHolder->getDbData('sales')
        );
        $command = 'php -f ' . BP . '/bin/magento setup:db-schema:split-sales ' .
            implode(" ", array_keys($installParams)) .
            ' -vvv --magento-init-params=' .
            $initParams['magento-init-params'];

        $this->shell->execute($command, array_values($installParams));
    }

    /**
     * Clean all types of cache
     */
    public function cacheClean()
    {
        $initParams = $this->parametersHolder->getInitParams();
        $command = 'php -f ' . BP . '/bin/magento cache:clean ' .
            ' -vvv --magento-init-params=' .
            $initParams['magento-init-params'];

        $this->shell->execute($command);
    }

    /**
     * Convert from raw params to CLI arguments, like --admin-username.
     *
     * @param  array $params
     * @return array
     */
    private function toCliArguments(array $params)
    {
        $result = [];

        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $result["--{$key}=%s"] = $value;
            }
        }

        return $result;
    }

    /**
     * Execute install command.
     *
     * @param array $modules
     * @param array $installParams
     * @return string
     * @throws \Exception
     */
    public function install(array $modules, array $installParams = [])
    {
        if (empty($modules)) {
            throw new \Exception("Cannot install Magento without modules");
        }

        $params = $this->parametersHolder->getInitParams();
        $installParams += [
            InstallCommand::INPUT_KEY_ENABLE_MODULES => implode(",", $modules),
            InstallCommand::INPUT_KEY_DISABLE_MODULES => 'all'
        ];
        $installParams = $this->toCliArguments(
            array_merge(
                $params,
                $this->parametersHolder->getDbData('default'),
                $installParams
            )
        );
        // run install script
        return $this->shell->execute(
            PHP_BINARY . ' -f %s setup:install -vvv ' . implode(' ', array_keys($installParams)),
            array_merge([BP . '/bin/magento'], array_values($installParams))
        );
    }
}
