<?php
/**
 * ObjectManager config with interception processing
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\ObjectManager\Config;

use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Framework\ObjectManager\RelationsInterface;
use Magento\Framework\ObjectManager\InterceptableValidator;

/**
 * Class \Magento\Framework\Interception\ObjectManager\Config\Developer
 *
 * @since 2.0.0
 */
class Developer extends \Magento\Framework\ObjectManager\Config\Config implements ConfigInterface
{
    /**
     * @var InterceptableValidator
     * @since 2.1.0
     */
    private $interceptableValidator;

    /**
     * @param RelationsInterface $relations
     * @param DefinitionInterface $definitions
     * @param InterceptableValidator $interceptableValidator
     * @since 2.1.0
     */
    public function __construct(
        RelationsInterface $relations = null,
        DefinitionInterface $definitions = null,
        InterceptableValidator $interceptableValidator = null
    ) {
        $this->interceptableValidator = $interceptableValidator ?: new InterceptableValidator();
        parent::__construct($relations, $definitions);
    }

    /**
     * @var \Magento\Framework\Interception\ConfigInterface
     * @since 2.0.0
     */
    protected $interceptionConfig;

    /**
     * Set Interception config
     *
     * @param \Magento\Framework\Interception\ConfigInterface $interceptionConfig
     * @return void
     * @since 2.0.0
     */
    public function setInterceptionConfig(\Magento\Framework\Interception\ConfigInterface $interceptionConfig)
    {
        $this->interceptionConfig = $interceptionConfig;
    }

    /**
     * Retrieve instance type with interception processing
     *
     * @param string $instanceName
     * @return string
     * @since 2.0.0
     */
    public function getInstanceType($instanceName)
    {
        $type = parent::getInstanceType($instanceName);
        if ($this->interceptionConfig && $this->interceptionConfig->hasPlugins($instanceName)
            && $this->interceptableValidator->validate($instanceName)
        ) {
            return $type . '\\Interceptor';
        }
        return $type;
    }

    /**
     * Retrieve instance type without interception processing
     *
     * @param string $instanceName
     * @return string
     * @since 2.0.0
     */
    public function getOriginalInstanceType($instanceName)
    {
        return parent::getInstanceType($instanceName);
    }
}
