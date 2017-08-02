<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Spi;

/**
 * Allows to use custom callbacks and functions before applying fallback
 * @since 2.2.0
 */
interface PreProcessorInterface
{
    /**
     * Pre-processing of config
     *
     * @param array $config
     * @return array
     * @since 2.2.0
     */
    public function process(array $config);
}
