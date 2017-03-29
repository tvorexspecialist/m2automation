<?php
/**
 * Phrase renderer interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase;

/**
 * Translated phrase renderer
 *
 * @api
 */
interface RendererInterface
{
    /**
     * Render source text
     *
     * @param [] $source
     * @param [] $arguments
     * @return string
     */
    public function render(array $source, array $arguments);
}
