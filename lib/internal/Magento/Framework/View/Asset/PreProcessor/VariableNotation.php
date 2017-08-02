<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\View\Asset;
use Magento\Framework\View\Asset\NotationResolver;
use Magento\Framework\View\Url\CssResolver;

/**
 * Support of notation "{{variable}}" in CSS-files
 *
 * Used to replace placeholder variables (such as {{base_url_path}}) with dynamic values.
 * @since 2.0.0
 */
class VariableNotation implements Asset\PreProcessorInterface
{
    /**
     * @var \Magento\Framework\View\Url\CssResolver
     * @since 2.0.0
     */
    private $cssResolver;

    /**
     * @var NotationResolver\Variable
     * @since 2.0.0
     */
    private $notationResolver;

    /**
     * Constructor
     *
     * @param CssResolver $cssResolver
     * @param NotationResolver\Variable $notationResolver
     * @since 2.0.0
     */
    public function __construct(
        CssResolver $cssResolver,
        NotationResolver\Variable $notationResolver
    ) {
        $this->cssResolver = $cssResolver;
        $this->notationResolver = $notationResolver;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function process(Chain $chain)
    {
        $callback = function ($path) {
            return $this->notationResolver->convertVariableNotation($path);
        };
        $chain->setContent($this->cssResolver->replaceRelativeUrls($chain->getContent(), $callback));
    }
}
