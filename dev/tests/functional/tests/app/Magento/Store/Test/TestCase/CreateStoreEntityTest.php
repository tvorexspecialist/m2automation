<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\StoreNew;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for CreateStoreEntity (Store Management)
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create Store Group
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Stores -> All Stores
 * 3. Click "Create Store View" button
 * 4. Fill data according to dataset
 * 5. Perform all assertions
 *
 * @group Store_Management_(PS)
 * @ZephyrId MAGETWO-27647
 */
class CreateStoreEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    /* end tags */

    /**
     * Page StoreIndex
     *
     * @var StoreIndex
     */
    protected $storeIndex;

    /**
     * Page StoreNew
     *
     * @var StoreNew
     */
    protected $storeNew;

    /**
     * Preparing pages for test
     *
     * @param StoreIndex $storeIndex
     * @param StoreNew $storeNew
     * @return void
     */
    public function __inject(StoreIndex $storeIndex, StoreNew $storeNew)
    {
        $this->storeIndex = $storeIndex;
        $this->storeNew = $storeNew;
    }

    /**
     * Runs Test Creation for StoreEntityTest
     *
     * @param Store $store
     * @return void
     */
    public function test(Store $store)
    {
        //Steps:
        $this->storeIndex->open();
        $this->storeIndex->getGridPageActions()->addStoreView();
        $this->storeNew->getStoreForm()->fill($store);
        $this->storeNew->getFormPageActions()->save();
    }
}
