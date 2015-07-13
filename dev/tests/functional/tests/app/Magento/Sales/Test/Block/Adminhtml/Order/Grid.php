<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Backend Data Grid for managing "Sales Order" entities.
 */
class Grid extends DataGrid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => '[name="filters[increment_id]"]',
        ],
        'status' => [
            'selector' => '[name="filters[status]"]',
            'input' => 'select',
        ],
        'purchase_date_from' => [
            'selector' => '[name="filters[created_at][from]"]',
        ],
        'purchase_date_to' => [
            'selector' => '[name="filters[created_at][to]"]',
        ],
        'base_grand_total_from' => [
            'selector' => '[name="filters[base_grand_total][from]"]',
        ],
        'base_grand_total_to' => [
            'selector' => '[name="filters[base_grand_total][to]"]',
        ],
        'purchased_gran_total_from' => [
            'selector' => '[name="filters[grand_total][from]"]',
        ],
        'purchased_gran_total_to' => [
            'selector' => '[name="filters[grand_total][to]"]',
        ],
        'purchase_point' => [
            'selector' => '[name="filters[store_id]"]',
            'input' => 'selectstore'
        ],
        'bill_to_name' => [
            'selector' => '[name="filters[billing_name]"]'
        ],
        'ship_to_name' => [
            'selector' => '[name="filters[shipping_name]"]',
        ]
    ];

    /**
     * @var string
     */
    protected $createNewOrder = '[data-ui-id="add-button"]';

    /**
     * Purchase Point Filter selector.
     *
     * @var string
     */
    protected $purchasePointFilter = '[name="filters[store_id]"]';

    /**
     * Order Id td selector.
     *
     * @var string
     */
    protected $editLink = 'a.action-menu-item';

    /**
     * First row selector.
     *
     * @var string
     */
    protected $firstRowSelector = '//tbody/tr[1]//a';

    /**
     * Start to create new order.
     */
    public function addNewOrder()
    {
        $this->_rootElement->find($this->createNewOrder)->click();
    }

    /**
     * Get StoreGroup list of Purchase Point on filter.
     *
     * @return array
     */
    public function getPurchasePointStoreGroups()
    {
        $this->openFilterBlock();
        $storeGroupElements = $this->_rootElement->find($this->purchasePointFilter)
            ->getElements('//option/preceding-sibling::optgroup[1]', Locator::SELECTOR_XPATH);
        $result = [];

        foreach ($storeGroupElements as $storeGroupElement) {
            $result[] = trim($storeGroupElement->getAttribute('label'));
        }

        return $result;
    }
}
