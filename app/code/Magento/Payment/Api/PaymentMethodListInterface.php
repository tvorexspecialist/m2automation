<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Api;

/**
 * Payment method list interface.
 *
 * @api
 * @since 2.2.0
 */
interface PaymentMethodListInterface
{
    /**
     * Get list of payment methods.
     *
     * @param int $storeId
     * @return \Magento\Payment\Api\Data\PaymentMethodInterface[]
     * @since 2.2.0
     */
    public function getList($storeId);

    /**
     * Get list of active payment methods.
     *
     * @param int $storeId
     * @return \Magento\Payment\Api\Data\PaymentMethodInterface[]
     * @since 2.2.0
     */
    public function getActiveList($storeId);
}
