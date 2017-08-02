<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget;

/**
 * Class \Magento\Widget\Controller\Adminhtml\Widget\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Widget\Model\Widget\Config
     * @since 2.0.0
     */
    protected $_widgetConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Widget\Model\Widget\Config $widgetConfig
     * @param \Magento\Framework\Registry $coreRegistry
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Widget\Model\Widget\Config $widgetConfig,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_widgetConfig = $widgetConfig;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Wisywyg widget plugin main page
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        // save extra params for widgets insertion form
        $skipped = $this->getRequest()->getParam('skip_widgets');
        $skipped = $this->_widgetConfig->decodeWidgetsFromQuery($skipped);

        $this->_coreRegistry->register('skip_widgets', $skipped);

        $this->_view->loadLayout('empty')->renderLayout();
    }
}
