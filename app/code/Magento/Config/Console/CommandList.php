<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console;

use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\Console\CommandListInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * {@inheritdoc}
 */
class CommandList implements CommandListInterface
{
    /**
     * The Object Manager.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager The Object Manager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands()
    {
        $commands = [];
        $commandClasses = [
            ConfigSetCommand::class,
        ];

        foreach ($commandClasses as $class) {
            $commands[] = $this->objectManager->get($class);
        }

        return $commands;
    }
}
