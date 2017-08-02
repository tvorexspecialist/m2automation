<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\GuestBillingAddressManagementInterface;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Billing address management service for guest carts.
 * @since 2.0.0
 */
class GuestBillingAddressManagement implements GuestBillingAddressManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     * @since 2.0.0
     */
    private $quoteIdMaskFactory;

    /**
     * @var BillingAddressManagementInterface
     * @since 2.0.0
     */
    private $billingAddressManagement;

    /**
     * Constructs a quote billing address service object.
     *
     * @param BillingAddressManagementInterface $billingAddressManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @since 2.0.0
     */
    public function __construct(
        BillingAddressManagementInterface $billingAddressManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->billingAddressManagement = $billingAddressManagement;
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address, $useForShipping = false)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->billingAddressManagement->assign($quoteIdMask->getQuoteId(), $address, $useForShipping);
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->billingAddressManagement->get($quoteIdMask->getQuoteId());
    }
}
