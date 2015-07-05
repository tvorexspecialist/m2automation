<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Interface FilterApplierInterface
 */
interface FilterApplierInterface
{
    /**
     * Apply filter
     *
     * @param AbstractDb $collection
     * @param $filters
     * @return mixed
     */
    public function apply(AbstractDb $collection, $filters);
}