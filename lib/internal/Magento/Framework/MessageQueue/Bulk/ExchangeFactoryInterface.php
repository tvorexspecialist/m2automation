<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Bulk;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\Bulk\ExchangeInterface
 *
 * @api
 * @since 100.2.0
 */
interface ExchangeFactoryInterface
{
    /**
     * Create exchange instance.
     *
     * @param string $connectionName
     * @param array $data
     * @return ExchangeInterface
     * @throws \LogicException If exchange is not defined for the specified connection type
     *                          or it doesn't implement ExchangeInterface
     * @since 100.2.0
     */
    public function create($connectionName, array $data = []);
}
