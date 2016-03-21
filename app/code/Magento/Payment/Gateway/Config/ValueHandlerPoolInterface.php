<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Config;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface ValueHandlerPoolInterface
 * @package Magento\Payment\Gateway\Config
 * @api
 */
interface ValueHandlerPoolInterface
{
    /**
     * Retrieves an appropriate configuration value handler
     *
     * @param string $field
     * @return ValueHandlerInterface
     * @throws NotFoundException
     */
    public function get($field);
}
