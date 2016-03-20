<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write\Create;

use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Db\CreateRow;

/**
 * Class CreateMain
 */
class CreateMain
{
    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @var CreateRow
     */
    private $createRow;

    /**
     * CreateMain constructor.
     *
     * @param HydratorPool $hydratorPool
     * @param CreateRow $createRow
     */
    public function __construct(
        HydratorPool $hydratorPool,
        CreateRow $createRow
    ) {
        $this->hydratorPool = $hydratorPool;
        $this->createRow = $createRow;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     */
    public function execute($entityType, $entity, $data = [])
    {
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $entityData = $this->createRow->execute(
            $entityType,
            array_merge($hydrator->extract($entity), $data)
        );
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
