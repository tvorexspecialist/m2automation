<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Rule;

/**
 * Class DateApplier
 * adds the dates just for SalesRule
 * @since 2.1.0
 */
class DateApplier
{
    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int|string $now
     * @return void
     * @since 2.1.0
     */
    public function applyDate($select, $now)
    {
        $select->where(
            'from_date is null or from_date <= ?',
            $now
        )->where(
            'to_date is null or to_date >= ?',
            $now
        );
    }
}
