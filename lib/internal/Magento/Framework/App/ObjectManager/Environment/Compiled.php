<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\ObjectManager\Environment;

use Magento\Framework\App\EnvironmentInterface;
use Magento\Framework\App\Interception\Cache\CompiledConfig;
use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\App\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Compiled extends AbstractEnvironment implements EnvironmentInterface
{
    /**#@+
     * Mode name
     */
    const MODE = 'compiled';
    /**#@- */

    /**
     * @var string
     * @since 2.0.0
     */
    protected $mode = self::MODE;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $configPreference = \Magento\Framework\ObjectManager\Factory\Compiled::class;

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled
     * @since 2.0.0
     */
    private $configLoader;

    /**
     * Creates factory
     *
     * @param array $arguments
     * @param string $factoryClass
     *
     * @return FactoryInterface
     * @since 2.0.0
     */
    protected function createFactory($arguments, $factoryClass)
    {
        return new $factoryClass(
            $this->getDiConfig(),
            $arguments['shared_instances'],
            $arguments
        );
    }

    /**
     * Returns initialized compiled config
     *
     * @return \Magento\Framework\Interception\ObjectManager\ConfigInterface
     * @since 2.0.0
     */
    public function getDiConfig()
    {
        if (!$this->config) {
            $this->config = new \Magento\Framework\Interception\ObjectManager\Config\Compiled(
                $this->getConfigData()
            );
        }

        return $this->config;
    }

    /**
     * Returns config data as array
     *
     * @return array
     * @since 2.0.0
     */
    protected function getConfigData()
    {
        return $this->getObjectManagerConfigLoader()->load(Area::AREA_GLOBAL);
    }

    /**
     * Returns new instance of compiled config loader
     *
     * @return \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled
     * @since 2.0.0
     */
    public function getObjectManagerConfigLoader()
    {
        if ($this->configLoader) {
            return $this->configLoader;
        }

        $this->configLoader = new \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled();
        return $this->configLoader;
    }

    /**
     * {inheritdoc}
     * @since 2.0.0
     */
    public function configureObjectManager(ConfigInterface $diConfig, &$sharedInstances)
    {
        $objectManager = ObjectManager::getInstance();

        $objectManager->configure(
            $objectManager
                ->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class)
                ->load(Area::AREA_GLOBAL)
        );
        $objectManager->get(\Magento\Framework\Config\ScopeInterface::class)
            ->setCurrentScope('global');
        $diConfig->setInterceptionConfig(
            $objectManager->get(\Magento\Framework\Interception\Config\Config::class)
        );
        $sharedInstances[\Magento\Framework\Interception\PluginList\PluginList::class] = $objectManager->create(
            \Magento\Framework\Interception\PluginListInterface::class,
            ['cache' => $objectManager->get(\Magento\Framework\App\Interception\Cache\CompiledConfig::class)]
        );
        $objectManager
            ->get(\Magento\Framework\App\Cache\Manager::class)
            ->setEnabled([CompiledConfig::TYPE_IDENTIFIER], true);
    }
}
