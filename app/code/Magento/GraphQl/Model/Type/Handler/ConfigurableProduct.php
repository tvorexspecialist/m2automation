<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Define ConfigurableProduct's GraphQL type
 */
class ConfigurableProduct implements HandlerInterface
{
    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param TypeFactory $typeFactory
     */
    public function __construct(Pool $typePool, TypeFactory $typeFactory)
    {
        $this->typePool = $typePool;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        $fields = [];
        $interface = $this->typePool->getType('Product');
        $fields = array_merge($fields, $interface->config['fields']);
        $fields['configurable_product_links'] =  $this->typeFactory->createList(
            $this->typePool->getComplexType('SimpleProduct')
        );

        return $this->typeFactory->createObject(
            [
                'name' => $reflector->getShortName(),
                'fields' => $fields,
                'interfaces' => [$interface]
            ]
        );
    }
}
