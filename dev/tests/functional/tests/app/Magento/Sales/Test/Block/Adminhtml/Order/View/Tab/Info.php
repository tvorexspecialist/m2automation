<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Order information tab block.
 */
class Info extends Tab
{
    /**
     * Order status selector.
     *
     * @var string
     */
    protected $orderStatus = '#order_status';

    /**
     * Selector for 'Payment Information' block.
     *
     * @var string
     */
    private $paymentInfoBlockSelector = '.order-payment-method';

    /**
     * Get order status from info block.
     *
     * @return array|string
     */
    public function getOrderStatus()
    {
        return $this->_rootElement->find($this->orderStatus)->getText();
    }

    /**
     * Returns Payment Information block.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info\PaymentInfoBlock
     */
    public function getPaymentInfoBlock()
    {
        return $this->blockFactory->create(
            \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info\PaymentInfoBlock::class,
            ['element' => $this->_rootElement->find($this->paymentInfoBlockSelector)]
        );
    }
}
