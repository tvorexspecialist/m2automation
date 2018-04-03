<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;

/**
 * Resolver fetches the data and formats it according to the GraphQL schema.
 */
interface ResolverInterface
{
    /**
     * Fetches the data from persistence models and format it according to the GraphQL schema.
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return Value
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) : Value;
}
