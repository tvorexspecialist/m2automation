<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

/**
 * @api
 * @method string getImageUrl()
 * @method string getWidth()
 * @method string getHeight()
 * @method string getLabel()
 * @method mixed getResizedImageWidth()
 * @method mixed getResizedImageHeight()
 * @method float getRatio()
 * @method string getCustomAttributes()
 * @since 100.0.2
 */
class Image extends \Magento\Framework\View\Element\Template
{
    /**
     * @deprecated since version 2.2-develop
     */
    protected $imageHelper;
    
    /**
     * @deprecated since version 2.2-develop
     */
    protected $product;
    
    /**
     * @deprecated since version 2.2-develop
     */
    protected $attributes = [];
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        if (isset($data['template'])) {
            $this->setTemplate($data['template']);
            unset($data['template']);
        }
        parent::__construct($context, $data);
    }
}
