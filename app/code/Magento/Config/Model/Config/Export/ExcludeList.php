<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Export;

/**
 * Class ExcludeList contains list of config fields which should be excluded from config export file.
 *
 * @deprecated 2.2.0 because in Magento since version 2.2.0 there are several
 * types for configuration fields that require special processing.
 * @see \Magento\Config\Model\Config\TypePool
 * @since 2.1.3
 */
class ExcludeList
{
    /**
     * @var array
     * @since 2.1.3
     */
    private $configs;

    /**
     * @param array $configs
     * @since 2.1.3
     */
    public function __construct(array $configs = [])
    {
        $this->configs = $configs;
    }

    /**
     * Check whether config item is excluded from export
     *
     * @param string $path
     * @return bool
     * @deprecated 2.2.0
     * @since 2.1.3
     */
    public function isPresent($path)
    {
        return !empty($this->configs[$path]);
    }

    /**
     * Retrieves all excluded field paths for export
     *
     * @return array
     * @deprecated 2.2.0
     * @since 2.1.3
     */
    public function get()
    {
        return array_keys(
            array_filter(
                $this->configs,
                function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }
            )
        );
    }
}
