<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\IndexerInterface as SaveHandlerInterface;

class SaveHandlerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get handler class instance
     *
     * @param string $saveHandlerClass
     * @param array $arguments
     * @throws \InvalidArgumentException
     * @return IndexerInterface
     */
    public function create($saveHandlerClass, $arguments = [])
    {
        $handler = $this->objectManager->create($saveHandlerClass, $arguments);
        if (!$handler instanceof SaveHandlerInterface) {
            throw new \InvalidArgumentException(
                $saveHandlerClass . ' doesn\'t implement \Magento\Framework\IndexerInterface'
            );
        }

        return $handler;
    }
}
