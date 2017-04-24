<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Config;

/**
 * Interface ValueHandlerInterface
 * @package Magento\Payment\Gateway\Config
 * @api
 */
interface ValueHandlerInterface
{
    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function handle(array $subject, $storeId = null);
}
