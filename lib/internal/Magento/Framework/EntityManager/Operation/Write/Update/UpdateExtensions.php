<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write\Update;

use Magento\Framework\EntityManager\Operation\ExtensionPool;

/**
 * Class UpdateExtensions
 */
class UpdateExtensions
{
    /**
     * @var ExtensionPool
     */
    private $extensionPool;

    /**
     * CreateExtensions constructor.
     * @param ExtensionPool $extensionPool
     */
    public function __construct(
        ExtensionPool $extensionPool
    ) {
        $this->extensionPool = $extensionPool;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     */
    public function execute($entityType, $entity)
    {
        $actions = $this->extensionPool->getActions($entityType, 'update');
        foreach ($actions as $action) {
            $entity = $action->execute($entityType, $entity);
        }
        return $entity;
    }
}
