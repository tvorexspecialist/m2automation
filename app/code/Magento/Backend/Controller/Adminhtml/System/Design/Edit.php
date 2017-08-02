<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

/**
 * Class \Magento\Backend\Controller\Adminhtml\System\Design\Edit
 *
 * @since 2.0.0
 */
class Edit extends \Magento\Backend\Controller\Adminhtml\System\Design
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::system_design_schedule');
        $resultPage->getConfig()->getTitle()->prepend(__('Store Design'));
        $id = (int)$this->getRequest()->getParam('id');
        $design = $this->_objectManager->create(\Magento\Framework\App\DesignInterface::class);

        if ($id) {
            $design->load($id);
        }

        $resultPage->getConfig()->getTitle()->prepend(
            $design->getId() ? __('Edit Store Design Change') : __('New Store Design Change')
        );

        $this->_coreRegistry->register('design', $design);

        $resultPage->addContent($resultPage->getLayout()->createBlock(
            \Magento\Backend\Block\System\Design\Edit::class
        ));
        $resultPage->addLeft(
            $resultPage->getLayout()->createBlock(\Magento\Backend\Block\System\Design\Edit\Tabs::class, 'design_tabs')
        );

        return $resultPage;
    }
}
