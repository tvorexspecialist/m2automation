<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockStatusCriteriaInterface
 * @api
 * @since 100.0.2
 *
 * @deprecated CatalogInventory will be replaced by Multi-Source Inventory (MSI)
 *             see https://devdocs.magento.com/guides/v2.3/rest/modules/inventory/inventory.html
 */
interface StockStatusCriteriaInterface extends \Magento\Framework\Api\CriteriaInterface
{
    /**
     * Add Criteria object
     *
     * @param \Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria
     * @return bool
     */
    public function addCriteria(\Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria);

    /**
     * Filter by scope(s)
     *
     * @param int $scope
     * @return bool
     */
    public function setScopeFilter($scope);

    /**
     * Add product(s) filter
     *
     * @param int $products
     * @return bool
     */
    public function setProductsFilter($products);

    /**
     * Add filter by quantity
     *
     * @param float $qty
     * @return bool
     */
    public function setQtyFilter($qty);
}
