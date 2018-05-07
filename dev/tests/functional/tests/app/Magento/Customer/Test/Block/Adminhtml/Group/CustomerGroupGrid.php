<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml\Group;

use \Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class CustomerGroupGrid
 * Adminhtml customer group grid
 */
class CustomerGroupGrid extends DataGrid
{
    /**
     * Select action toggle.
     *
     * @var string
     */
    protected $selectAction = '.action-select';

    /**
     * Initialize block elements
     *
     * @var array $filters
     */
    protected $filters = [
        'code' => [
            'selector' => '.admin__data-grid-filters input[name*=customer_group_code]',
        ],
    ];

    /**
     * Click on "Edit" link.
     *
     * @param SimpleElement $rowItem
     * @return void
     */
    protected function clickEditLink(SimpleElement $rowItem)
    {
        if ($rowItem->find($this->selectAction)->isVisible()) {
            $rowItem->find($this->selectAction)->click();
        }
        $rowItem->find($this->editLink)->click();
    }
}
