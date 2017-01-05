<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Structure\SearchInterface;
use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory as ConfigValueFactory;

class Subscription
{
    /**
     * Path to field subscription enabled into config structure.
     *
     * @var string
     */
    private $enabledConfigStructurePath = 'analytics/general/enabled';

    /**
     * Value which equal Yes for Yesno dropdown.
     *
     * @var int
     */
    private $yesValueDropdown = 1;

    /**
     * Resource for storing store configuration values.
     *
     * @var ConfigValueFactory
     */
    private $configValueFactory;

    /**
     * Config structure object which allow get field into config by path.
     *
     * @var SearchInterface
     */
    private $configStructure;

    /**
     * Resource model for config values.
     *
     * @var Data
     */
    private $configValueResource;

    /**
     * @param ConfigValueFactory $configValueFactory
     * @param SearchInterface $configStructure
     * @param Data $configValueResource
     */
    public function __construct(
        ConfigValueFactory $configValueFactory,
        SearchInterface $configStructure,
        Data $configValueResource
    ) {
        $this->configValueFactory = $configValueFactory;
        $this->configStructure = $configStructure;
        $this->configValueResource = $configValueResource;
    }

    /**
     * Set subscription enabled config value.
     *
     * @return boolean
     */
    public function enable()
    {
        /** @var Field $field */
        $field = $this->configStructure->getElement($this->enabledConfigStructurePath);
        /** @var Value $configValue */
        $configValue = $field->hasBackendModel()
            ? $field->getBackendModel()
            : $this->configValueFactory->create();
        $configPath = $field->getConfigPath() ?: $this->enabledConfigStructurePath;

        $this->configValueResource
            ->load($configValue, $configPath, 'path');

        $configValue->setValue($this->yesValueDropdown);
        $configValue->setPath($configPath);

        $this->configValueResource
            ->save($configValue);

        return true;
    }
}
