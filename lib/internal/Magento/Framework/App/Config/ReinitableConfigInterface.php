<?php
/**
 * Configuration Reinitable Interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Config;

/**
 * @api
 */
interface ReinitableConfigInterface extends \Magento\Framework\App\Config\MutableScopeConfigInterface
{
    /**
     * Reinitialize config object
     *
     * @return \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    public function reinit();
}
