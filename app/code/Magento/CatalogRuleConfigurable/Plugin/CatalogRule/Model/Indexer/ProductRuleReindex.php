<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Indexer;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider;

/**
 * Class ReindexProduct. Add configurable sub-products to reindex
 * @since 2.0.0
 */
class ProductRuleReindex
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     * @since 2.0.0
     */
    private $configurable;

    /**
     * @var \Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider
     * @since 2.0.0
     */
    private $configurableProductsProvider;

    /**
     * @param Configurable $configurable
     * @param ConfigurableProductsProvider $configurableProductsProvider
     * @since 2.0.0
     */
    public function __construct(
        Configurable $configurable,
        ConfigurableProductsProvider $configurableProductsProvider
    ) {
        $this->configurable = $configurable;
        $this->configurableProductsProvider = $configurableProductsProvider;
    }

    /**
     * @param \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject
     * @param \Closure $proceed
     * @param int $id
     *
     * @return void
     * @since 2.0.0
     */
    public function aroundExecuteRow(
        \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject,
        \Closure $proceed,
        $id
    ) {
        $configurableProductIds = $this->configurableProductsProvider->getIds([$id]);
        $this->reindexSubProducts($configurableProductIds, $subject);
        if (!$configurableProductIds) {
            $proceed($id);
        }
    }

    /**
     * @param \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject
     * @param \Closure $proceed
     * @param array $ids
     *
     * @return void
     * @since 2.0.0
     */
    public function aroundExecuteList(
        \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject,
        \Closure $proceed,
        array $ids
    ) {
        $configurableProductIds = $this->configurableProductsProvider->getIds($ids);
        $subProducts = $this->reindexSubProducts($configurableProductIds, $subject);
        $ids = array_diff($ids, $configurableProductIds, $subProducts);
        if ($ids) {
            $proceed($ids);
        }
    }

    /**
     * @param array $configurableIds
     * @param \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject
     *
     * @return array
     * @since 2.0.0
     */
    private function reindexSubProducts(
        array $configurableIds,
        \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject
    ) {
        $subProducts = [];
        if ($configurableIds) {
            $subProducts = array_values($this->configurable->getChildrenIds($configurableIds)[0]);
            if ($subProducts) {
                $subject->executeList($subProducts);
            }
        }
        return $subProducts;
    }
}
