<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class implementing the composite pattern on @see FormatterInterface::format method
 */
class FormatterComposite implements FormatterInterface
{

    /**
     * @var FormatterInterface[]
     */
    private $formatterInstances = [];

    /**
     * @param FormatterInterface[] $formatterInstances
     */
    public function __construct(array $formatterInstances)
    {
        $this->formatterInstances = $formatterInstances;
    }

    /**
     * Format single product data from object to an array
     *
     * {@inheritdoc}
     */
    public function format(ProductInterface $product, array $productData = [])
    {
        foreach ($this->formatterInstances as $formatterInstance) {
            $productData = $formatterInstance->format($product, $productData);
        }

        return $productData;
    }
}
