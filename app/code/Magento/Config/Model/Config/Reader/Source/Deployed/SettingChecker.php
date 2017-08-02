<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Reader\Source\Deployed;

use Magento\Config\Model\Config\Reader;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Framework\App\Config\ScopeCodeResolver;

/**
 * Class for checking settings that defined in config file
 * @api
 * @since 2.2.0
 */
class SettingChecker
{
    /**
     * @var DeploymentConfig
     * @since 2.2.0
     */
    private $config;

    /**
     * @var PlaceholderInterface
     * @since 2.2.0
     */
    private $placeholder;

    /**
     * @var ScopeCodeResolver
     * @since 2.2.0
     */
    private $scopeCodeResolver;

    /**
     * @param DeploymentConfig $config
     * @param PlaceholderFactory $placeholderFactory
     * @param ScopeCodeResolver $scopeCodeResolver
     * @since 2.2.0
     */
    public function __construct(
        DeploymentConfig $config,
        PlaceholderFactory $placeholderFactory,
        ScopeCodeResolver $scopeCodeResolver
    ) {
        $this->config = $config;
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->placeholder = $placeholderFactory->create(PlaceholderFactory::TYPE_ENVIRONMENT);
    }

    /**
     * Check that setting defined in deployed configuration
     *
     * @param string $path
     * @param string $scope
     * @param string|null $scopeCode
     * @return boolean
     * @since 2.2.0
     */
    public function isReadOnly($path, $scope, $scopeCode = null)
    {
        $config = $this->getEnvValue(
            $this->placeholder->generate($path, $scope, $scopeCode)
        );

        if (null === $config) {
            $config = $this->config->get($this->resolvePath($scope, $scopeCode) . "/" . $path);
        }

        return $config !== null;
    }

    /**
     * Check that there is value for generated placeholder
     *
     * Placeholder is generated from values of $path, $scope and $scopeCode
     *
     * @param string $path
     * @param string $scope
     * @param string $scopeCode
     * @param string|null $scopeCode
     * @return string|null
     * @since 2.2.0
     */
    public function getPlaceholderValue($path, $scope, $scopeCode = null)
    {
        return $this->getEnvValue($this->placeholder->generate($path, $scope, $scopeCode));
    }

    /**
     * Retrieve value of environment variable by placeholder
     *
     * @param string $placeholder
     * @return string|null
     * @since 2.2.0
     */
    public function getEnvValue($placeholder)
    {
        if ($this->placeholder->isApplicable($placeholder) && isset($_ENV[$placeholder])) {
            return $_ENV[$placeholder];
        }

        return null;
    }

    /**
     * Resolve path by scope and scope code
     *
     * @param string $scope
     * @param string $scopeCode
     * @return string
     * @since 2.2.0
     */
    private function resolvePath($scope, $scopeCode)
    {
        $scopePath = 'system/' . $scope;

        if ($scope != ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $scopePath .= '/' . $this->scopeCodeResolver->resolve($scope, $scopeCode);
        }

        return $scopePath;
    }
}
