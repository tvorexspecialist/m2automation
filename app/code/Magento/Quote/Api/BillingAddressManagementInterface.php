<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

/**
 * Interface BillingAddressManagementInterface
 * @api
 * @since 2.0.0
 */
interface BillingAddressManagementInterface
{
    /**
     * Assigns a specified billing address to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Quote\Api\Data\AddressInterface $address Billing address data.
     * @param bool $useForShipping
     * @return int Address ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException The specified cart ID or address data is not valid.
     * @since 2.0.0
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address, $useForShipping = false);

    /**
     * Returns the billing address for a specified quote.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\AddressInterface Quote billing address object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @since 2.0.0
     */
    public function get($cartId);
}
