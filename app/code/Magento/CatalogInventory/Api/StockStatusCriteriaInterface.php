<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockStatusCriteriaInterface
 * @api
 */
interface StockStatusCriteriaInterface extends \Magento\Framework\Api\CriteriaInterface
{
    /**
     * Add Criteria object
     *
     * @param \Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria
     * @return void
     */
    public function addCriteria(\Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria);

    /**
     * Filter by scope(s)
     *
     * @param int $scope
     * @return void
     */
    public function setScopeFilter($scope);

    /**
     * Add product(s) filter
     *
     * @param int $products
     * @return void
     */
    public function setProductsFilter($products);

    /**
     * Add filter by quantity
     *
     * @param float $qty
     * @return void
     */
    public function setQtyFilter($qty);
}
