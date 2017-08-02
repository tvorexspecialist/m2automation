<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Block\Email;

/**
 * ProductAlert email back in stock grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Stock extends \Magento\ProductAlert\Block\Email\AbstractEmail
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'email/stock.phtml';

    /**
     * Retrieve unsubscribe url for product
     *
     * @param int $productId
     * @return string
     * @since 2.0.0
     */
    public function getProductUnsubscribeUrl($productId)
    {
        $params = $this->_getUrlParams();
        $params['product'] = $productId;
        return $this->getUrl('productalert/unsubscribe/stock', $params);
    }

    /**
     * Retrieve unsubscribe url for all products
     *
     * @return string
     * @since 2.0.0
     */
    public function getUnsubscribeUrl()
    {
        return $this->getUrl('productalert/unsubscribe/stockAll', $this->_getUrlParams());
    }
}
