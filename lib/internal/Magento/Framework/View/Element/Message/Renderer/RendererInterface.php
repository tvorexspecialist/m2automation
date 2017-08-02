<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;

/**
 * Interface \Magento\Framework\View\Element\Message\Renderer\RendererInterface
 *
 * @since 2.0.0
 */
interface RendererInterface
{
    /**
     * Renders message
     *
     * @param MessageInterface $message
     * @param array $initializationData
     * @return string
     * @since 2.0.0
     */
    public function render(MessageInterface $message, array $initializationData);
}
