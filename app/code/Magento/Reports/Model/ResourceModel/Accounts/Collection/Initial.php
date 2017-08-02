<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customers by totals Report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Accounts\Collection;

/**
 * @api
 * @since 2.0.0
 */
class Initial extends \Magento\Reports\Model\ResourceModel\Report\Collection
{
    /**
     * Report sub-collection class name
     * @var string
     * @since 2.0.0
     */
    protected $_reportCollection = \Magento\Reports\Model\ResourceModel\Accounts\Collection::class;
}
