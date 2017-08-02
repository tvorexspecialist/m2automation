<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\ObjectManager\FactoryInterface;

/**
 * Direct usage of this class is strictly discouraged.
 *
 * Wrapper around object manager with workarounds to access it in client code.
 * Provides static access to objectManager, that is required for unserialization of objects.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class ObjectManager extends \Magento\Framework\ObjectManager\ObjectManager
{
    /**
     * @var ObjectManager
     * @since 2.0.0
     */
    protected static $_instance;

    /**
     * Retrieve object manager
     *
     * @return ObjectManager
     * @throws \RuntimeException
     * @since 2.0.0
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof \Magento\Framework\ObjectManagerInterface) {
            throw new \RuntimeException('ObjectManager isn\'t initialized');
        }
        return self::$_instance;
    }

    /**
     * Set object manager instance
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @throws \LogicException
     * @return void
     * @since 2.0.0
     */
    public static function setInstance(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        self::$_instance = $objectManager;
    }

    /**
     * @param FactoryInterface $factory
     * @param \Magento\Framework\ObjectManager\ConfigInterface $config
     * @param array $sharedInstances
     * @since 2.0.0
     */
    public function __construct(
        FactoryInterface $factory,
        \Magento\Framework\ObjectManager\ConfigInterface $config,
        array &$sharedInstances = []
    ) {
        parent::__construct($factory, $config, $sharedInstances);
        self::$_instance = $this;
    }
}
