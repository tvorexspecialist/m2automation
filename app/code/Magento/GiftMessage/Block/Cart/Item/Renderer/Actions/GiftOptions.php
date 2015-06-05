<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Cart\Item\Renderer\Actions;

use \Magento\Backend\Block\Template\Context;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use \Magento\Framework\Json\Encoder;

class GiftOptions extends Generic
{
    /**
     * @var bool
     */
    protected $_isScopePrivate = false;

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var array|LayoutProcessorInterface[]
     */
    protected $layoutProcessors;

    /**
     * @var Encoder
     */
    protected $jsonEncoder;

    /**
     * @param Context $context
     * @param Encoder $jsonEncoder
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Context $context,
        Encoder $jsonEncoder,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->_isScopePrivate = true;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->layoutProcessors = $layoutProcessors;
    }

    /**
     * Return JS layout
     *
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout, $this->getItem());
        }
        return $this->jsonEncoder->encode($this->jsLayout);
    }
}
