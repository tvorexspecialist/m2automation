<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block;

/**
 * Shortcut block interface
 *
 * @api
 */
interface ShortcutInterface
{
    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias();
}
