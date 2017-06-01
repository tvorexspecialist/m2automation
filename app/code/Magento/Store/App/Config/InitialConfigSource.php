<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Config;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\DataObject;

class InitialConfigSource implements ConfigSourceInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var string
     */
    private $configType;

    /**
     * DataProvider constructor.
     *
     * @param Reader $reader
     * @param DeploymentConfig $deploymentConfig
     * @param string $configType
     */
    public function __construct(Reader $reader, DeploymentConfig $deploymentConfig, $configType)
    {
        $this->reader = $reader;
        $this->deploymentConfig = $deploymentConfig;
        $this->configType = $configType;
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        /**
         * Magento store configuration should not be read from file source
         * on installed instance.
         *
         * @see \Magento\Store\Model\Config\Importer To import store configs
         */
        if ($this->deploymentConfig->isAvailable()) {
            return [];
        }

        $data = new DataObject($this->reader->load());
        if ($path !== '' && $path !== null) {
            $path = '/' . $path;
        }

        return $data->getData($this->configType . $path) ?: [];
    }
}
