<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

class FieldsetPool
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get fieldset class instance
     *
     * @param string $fieldsetClass
     * @throws \InvalidArgumentException
     * @return FieldsetInterface
     */
    public function get($fieldsetClass)
    {
        $handler = $this->objectManager->get($fieldsetClass);
        if (!$handler instanceof FieldsetInterface) {
            throw new \InvalidArgumentException(
                $fieldsetClass . ' doesn\'t implement \Magento\Indexer\Model\FieldsetInterface'
            );
        }

        return $handler;
    }
}
