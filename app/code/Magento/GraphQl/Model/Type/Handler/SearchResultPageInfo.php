<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Define SearchResultPageInfo GraphQL type
 */
class SearchResultPageInfo implements HandlerInterface
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $pool
     * @param TypeFactory $typeFactory
     */
    public function __construct(Pool $pool, TypeFactory $typeFactory)
    {
        $this->pool = $pool;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return $this->typeFactory->createObject(
            [
                'name' => $reflector->getShortName(),
                'fields' => $this->getFields()
            ]
        );
    }

    /**
     * Retrieve fields
     *
     * @return array
     */
    private function getFields()
    {
        $intType = $this->pool->getType('Int');
        $result = [
            'page_size' => $intType,
            'current_page' => $intType
        ];

        return $result;
    }
}
