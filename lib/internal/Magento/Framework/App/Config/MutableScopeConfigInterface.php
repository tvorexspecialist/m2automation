<?php
/**
 * Configuration interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Config;

/**
 * @api
 * @since 2.0.0
 */
interface MutableScopeConfigInterface extends \Magento\Framework\App\Config\ScopeConfigInterface
{
    /**
     * Set config value in the corresponding config scope
     *
     * @param string $path
     * @param mixed $value
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return void
     * @since 2.0.0
     */
    public function setValue(
        $path,
        $value,
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    );
}
