<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Format a product's option information to conform to GraphQL schema representation
 */
class Options implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param ValueFactory $valueFactory
     */
    public function __construct(ValueFactory $valueFactory)
    {
        $this->valueFactory = $valueFactory;
    }

    /**
     * Format product's option data to conform to GraphQL schema
     *
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        array $value = null,
        array $args = null,
        $context,
        ResolveInfo $info
    ): ?Value {
        if (!isset($value['model'])) {
            return null;
        }

        /** @var Product $product */
        $product = $value['model'];

        $options = null;
        if (!empty($product->getOptions())) {
            $options = [];
            /** @var Option $option */
            foreach ($product->getOptions() as $key => $option) {
                $options[$key] = $option->getData();
                $options[$key]['required'] = $option->getIsRequire();
                $options[$key]['product_sku'] = $option->getProductSku();

                $values = $option->getValues() ?: [];
                /** @var Option\Value $value */
                foreach ($values as $valueKey => $value) {
                    $options[$key]['value'][$valueKey] = $value->getData();
                    $options[$key]['value'][$valueKey]['price_type']
                        = $value->getPriceType() !== null ? strtoupper($value->getPriceType()) : 'DYNAMIC';
                }

                if (empty($values)) {
                    $options[$key]['value'] = $option->getData();
                    $options[$key]['value']['price_type']
                        = $option->getPriceType() !== null ? strtoupper($option->getPriceType()) : 'DYNAMIC';
                }
            }
        }

        $result = function () use ($options) {
            return $options;
        };

        return $this->valueFactory->create($result);
    }
}
