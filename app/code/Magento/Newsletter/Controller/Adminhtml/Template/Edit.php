<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Template\Edit
 *
 * @since 2.0.0
 */
class Edit extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @since 2.0.0
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Edit Newsletter Template
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $model = $this->_objectManager->create(\Magento\Newsletter\Model\Template::class);
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $model->load($id);
        }

        $this->_coreRegistry->register('_current_template', $model);

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Newsletter::newsletter_template');

        if ($model->getId()) {
            $breadcrumbTitle = __('Edit Template');
            $breadcrumbLabel = $breadcrumbTitle;
        } else {
            $breadcrumbTitle = __('New Template');
            $breadcrumbLabel = __('Create Newsletter Template');
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Newsletter Templates'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getTemplateCode() : __('New Template')
        );

        $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

        // restore data
        $values = $this->_getSession()->getData('newsletter_template_form_data', true);
        if ($values) {
            $model->addData($values);
        }

        $this->_view->renderLayout();
    }
}
