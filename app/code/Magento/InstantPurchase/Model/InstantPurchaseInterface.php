<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Customer\Model\Customer;
use Magento\Store\Model\Store;

/**
 * Interface for detecting customer option to make instant purchase in a store.
 *
 * @api
 */
interface InstantPurchaseInterface
{
    /**
     * Detects instant purchase options for a customer in a store.
     *
     * @param Store $store
     * @param Customer $customer
     * @return InstantPurchaseOption
     */
    public function getOption(
        Store $store,
        Customer $customer
    ): InstantPurchaseOption;
}
