<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Model\Quote\Address;

/**
 * Class \Magento\OfflineShipping\Model\Quote\Address\FreeShipping
 *
 * @since 2.0.0
 */
class FreeShipping implements \Magento\Quote\Model\Quote\Address\FreeShippingInterface
{
    /**
     * @var \Magento\OfflineShipping\Model\SalesRule\Calculator
     * @since 2.0.0
     */
    protected $calculator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\OfflineShipping\Model\SalesRule\Calculator $calculator
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isFreeShipping(\Magento\Quote\Model\Quote $quote, $items)
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
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setFreeShipping(0);
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

            if ($addressFreeShipping && !$item->getAddress()->getFreeShipping()) {
                $item->getAddress()->setFreeShipping(true);
            }

            /** Parent free shipping we apply to all children*/
            $this->applyToChildren($item, $itemFreeShipping);
        }
        return (bool)$shippingAddress->getFreeShipping();
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param bool $isFreeShipping
     * @return void
     * @since 2.0.0
     */
    protected function applyToChildren(\Magento\Quote\Model\Quote\Item\AbstractItem $item, $isFreeShipping)
    {
        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            foreach ($item->getChildren() as $child) {
                $this->calculator->processFreeShipping($child);
                if ($isFreeShipping) {
                    $child->setFreeShipping($isFreeShipping);
                }
            }
        }
    }
}
