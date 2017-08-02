<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Resource collection for report rows
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Paypal\Model\ResourceModel\Report\Settlement\Row;

/**
 * Class \Magento\Paypal\Model\ResourceModel\Report\Settlement\Row\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initializing
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Paypal\Model\Report\Settlement\Row::class,
            \Magento\Paypal\Model\ResourceModel\Report\Settlement\Row::class
        );
    }

    /**
     * Join reports info table
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->join(
            ['report' => $this->getTable('paypal_settlement_report')],
            'report.report_id = main_table.report_id',
            ['report.account_id', 'report.report_date']
        );
        return $this;
    }

    /**
     * Filter items collection by account ID
     *
     * @param string $accountId
     * @return $this
     * @since 2.0.0
     */
    public function addAccountFilter($accountId)
    {
        $this->getSelect()->where('report.account_id = ?', $accountId);
        return $this;
    }
}
