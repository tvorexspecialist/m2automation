<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Paypal;

/**
 * PayPal Settlement Reports Controller
 * @since 2.0.0
 */
abstract class Reports extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Paypal::paypal_settlement_reports';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Paypal\Model\Report\Settlement\RowFactory
     * @since 2.0.0
     */
    protected $_rowFactory;

    /**
     * @var \Magento\Paypal\Model\Report\SettlementFactory
     * @since 2.0.0
     */
    protected $_settlementFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Paypal\Model\Report\Settlement\RowFactory $rowFactory
     * @param \Magento\Paypal\Model\Report\SettlementFactory $settlementFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Paypal\Model\Report\Settlement\RowFactory $rowFactory,
        \Magento\Paypal\Model\Report\SettlementFactory $settlementFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_rowFactory = $rowFactory;
        $this->_settlementFactory = $settlementFactory;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Initialize titles, navigation
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Paypal::report_salesroot_paypal_settlement_reports'
        )->_addBreadcrumb(
            __('Reports'),
            __('Reports')
        )->_addBreadcrumb(
            __('Sales'),
            __('Sales')
        )->_addBreadcrumb(
            __('PayPal Settlement Reports'),
            __('PayPal Settlement Reports')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('PayPal Settlement Reports'));
        return $this;
    }
}
