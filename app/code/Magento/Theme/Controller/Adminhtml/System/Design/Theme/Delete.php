<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

class Delete extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $themeId = $this->getRequest()->getParam('id');
        try {
            if ($themeId) {
                /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
                $theme = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface')->load($themeId);
                if (!$theme->getId()) {
                    throw new \InvalidArgumentException(sprintf('We cannot find a theme with id "%1".', $themeId));
                }
                if (!$theme->isVirtual()) {
                    throw new \InvalidArgumentException(
                        sprintf('Only virtual theme is possible to delete and theme "%s" isn\'t virtual', $themeId)
                    );
                }
                $theme->delete();
                $this->messageManager->addSuccess(__('You deleted the theme.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We cannot delete the theme.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        /**
         * @todo Temporary solution. Theme module should not know about the existence of editor module.
         */
        $path = (bool)$this->getRequest()->getParam('back', false)
            ? 'adminhtml/system_design_editor/index/'
            : 'adminhtml/*/';
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($path);
    }
}
