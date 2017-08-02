<?php
/**
 * DB configuration data converter. Converts associative array to tree array
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

/**
 * Class Converter.
 * @since 2.0.0
 */
class Converter extends \Magento\Framework\App\Config\Scope\Converter
{
    /**
     * Convert config data
     *
     * @param array $source
     * @param array $initialConfig
     * @return array
     * @since 2.0.0
     */
    public function convert($source, $initialConfig = [])
    {
        return array_replace_recursive($initialConfig, parent::convert($source));
    }
}
