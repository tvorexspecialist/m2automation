<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Model\Quote\Address;

use Magento\Quote\Model\Quote\Address;

class FreeShipping implements \Magento\Quote\Model\Quote\Address\FreeShippingInterface
{
    /**
     * @var \Magento\OfflineShipping\Model\SalesRule\Calculator
     */
    protected $calculator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\OfflineShipping\Model\SalesRule\Calculator $calculator
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\OfflineShipping\Model\SalesRule\Calculator $calculator
    ) {
        $this->storeManager = $storeManager;
        $this->calculator = $calculator;
    }

    /**
     * {@inheritDoc}
     */
    public function getFreeShipping(\Magento\Quote\Model\Quote $quote, $items)
    {
        /** @var \Magento\Quote\Api\Data\CartItemInterface[] $items */
        if (!count($items)) {
            return false;
        }

        $addressFreeShipping = true;
        $store = $this->storeManager->getStore($quote->getStoreId());
        $this->calculator->init(
            $store->getWebsiteId(),
            $quote->getCustomerGroupId(),
            $quote->getCouponCode()
        );

        /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
        foreach ($items as $item) {
            if ($item->getNoDiscount()) {
                $addressFreeShipping = false;
                $item->setFreeShipping(false);
                continue;
            }

            /** Child item discount we calculate for parent */
            if ($item->getParentItemId()) {
                continue;
            }

            $this->calculator->processFreeShipping($item);
            $itemFreeShipping = (bool)$item->getFreeShipping();
            $addressFreeShipping = $addressFreeShipping && $itemFreeShipping;

            /** Parent free shipping we apply to all children*/
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->calculator->processFreeShipping($child);
                    if ($itemFreeShipping) {
                        $child->setFreeShipping($itemFreeShipping);
                    }
                }
            }
        }
        return $addressFreeShipping;
    }
}
