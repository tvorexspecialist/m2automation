<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml\Edit\Tab\Addresses;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Class AddressesGrid
 * Backend customer addresses grid
 *
 */
class AddressesGrid extends DataGrid
{
    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = '//tr[@class="data-row"][1]//a[@data-action="item-edit"]';

    /**
     * First row selector
     *
     * @var string
     */
    protected $firstRowSelector = '//tr[@class="data-row"][1]';

    /**
     * Customer address grid loader.
     *
     * @var string
     */
    protected $loader = '.customer_form_areas_address_address_customer_address_listing [data-role="spinner"]';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'firstname' => [
            'selector' => '.admin__data-grid-filters input[name*=firstname]',
        ],
        'lastname' => [
            'selector' => '.admin__data-grid-filters input[name*=lastname]',
        ],
        'street' => [
            'selector' => '.admin__data-grid-filters input[name*=street]',
        ],
        'city' => [
            'selector' => '.admin__data-grid-filters input[name*=city]',
        ],
        'region_id' => [
            'selector' => '.admin__data-grid-filters input[name*=region]',
        ],
        'postcode' => [
            'selector' => '.admin__data-grid-filters input[name*=postcode]',
        ],
        'telephone' => [
            'selector' => '.admin__data-grid-filters input[name*=telephone]',
        ],
        'country_id' => [
            'selector' => '.admin__data-grid-filters select[name*=country]',
            'input' => 'select',
        ],

    ];

    /**
     * Select action toggle.
     *
     * @var string
     */
    private $selectAction = '.action-select';

    /**
     * Delete action toggle.
     *
     * @var string
     */
    private $deleteAddress = '[data-action="item-delete"]';

    /**
     * Locator value for "Edit" link inside action column.
     *
     * @var string
     */
    private $editAddress = '[data-action="item-edit"]';

    /**
     * Customer address modal window.
     *
     * @var string
     */
    private $customerAddressModalForm = '.customer_form_areas_address_address_customer_address_update_modal';

    /**
     * Search customer address by filter.
     *
     * @param array $filter
     */
    public function search(array $filter)
    {
        parent::search(array_intersect_key($filter, $this->filters));
    }

    /**
     * Search item and open it for editing.
     *
     * @param array $filter
     * @throws \Exception
     * @return void
     */
    public function searchAndOpen(array $filter)
    {
        $this->search($filter);
        $rowItem = $this->getRow([$filter['firstname']]);
        if ($rowItem->isVisible()) {
            $rowItem->find($this->selectAction)->click();
            $rowItem->find($this->editAddress)->click();
            $this->waitForElementVisible($this->customerAddressModalForm);
            $this->waitLoader();
        } else {
            throw new \Exception("Searched item was not found by filter\n" . print_r($filter, true));
        }
    }

    /**
     * Delete customer address by filter
     *
     * @param array $filter
     * @throws \Exception
     */
    public function deleteCustomerAddress(array $filter)
    {
        $this->search($filter);
        $rowItem = $this->getRow([$filter['firstname']]);
        if ($rowItem->isVisible()) {
            $rowItem->find($this->selectAction)->click();
            $rowItem->find($this->deleteAddress)->click();
            $modalElement = $this->browser->find($this->confirmModal);
            /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
            $modal = $this->blockFactory->create(
                \Magento\Ui\Test\Block\Adminhtml\Modal::class,
                ['element' => $modalElement]
            );
            $modal->acceptAlert();
            $this->waitLoader();
        } else {
            throw new \Exception("Searched item was not found by filter\n" . print_r($filter, true));
        }
    }

    /**
     * Open first row from the addresses grid
     *
     * @throws \Exception
     */
    public function openFirstRow()
    {
        $firstRow = $this->_rootElement->find($this->firstRowSelector, \Magento\Mtf\Client\Locator::SELECTOR_XPATH);
        if ($firstRow->isVisible()) {
            $firstRow->find($this->selectAction)->click();
            $firstRow->find($this->editAddress)->click();
            $this->waitForElementVisible($this->customerAddressModalForm);
            $this->waitLoader();
        } else {
            throw new \Exception("There is no any items in the grid");
        }
    }
}
