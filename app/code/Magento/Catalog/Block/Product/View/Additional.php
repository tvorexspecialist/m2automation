<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product additional info block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product\View;

/**
 * @api
 * @since 2.0.0
 */
class Additional extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_list;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'product/view/additional.phtml';

    /**
     * @return array
     * @since 2.0.0
     */
    public function getChildHtmlList()
    {
        if ($this->_list === null) {
            $this->_list = [];
            $layout = $this->getLayout();
            foreach ($this->getChildNames() as $name) {
                $this->_list[] = $layout->renderElement($name);
            }
        }
        return $this->_list;
    }
}
