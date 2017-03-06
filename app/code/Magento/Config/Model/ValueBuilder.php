<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;

/**
 * Builds instance \Magento\Framework\App\Config\Value with defined properties.
 */
class ValueBuilder
{
    /**
     * The deployment configuration reader.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * The manager for system configuration structure.
     *
     * @var StructureFactory
     */
    private $structureFactory;

    /**
     * The factory for configuration value objects.
     *
     * @see Value
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param DeploymentConfig $deploymentConfig The deployment configuration reader
     * @param StructureFactory $structureFactory The manager for system configuration structure
     * @param ValueFactory $valueFactory The factory for configuration value objects
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        StructureFactory $structureFactory,
        ValueFactory $valueFactory
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->structureFactory = $structureFactory;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Returns instance Value with defined properties.
     * @see Value
     *
     * @param string $path The configuration path in format group/section/field_name
     * @param string $value The configuration value
     * @param string $scope The configuration scope (default, website, or store)
     * @param string $scopeCode The scope code
     * @return Value
     */
    public function build($path, $value, $scope, $scopeCode)
    {
        /** @var Structure $structure */
        $structure = $this->structureFactory->create();
        /** @var Structure\Element\Field $field */
        $field = $this->deploymentConfig->isAvailable()
            ? $structure->getElement($path)
            : null;
        /** @var Value $backendModel */
        $backendModel = $field && $field->hasBackendModel()
            ? $field->getBackendModel()
            : $this->valueFactory->create();

        $backendModel->setPath($path);
        $backendModel->setScope($scope);
        $backendModel->setScopeId($scopeCode);
        $backendModel->setValue($value);

        return $backendModel;
    }
}
