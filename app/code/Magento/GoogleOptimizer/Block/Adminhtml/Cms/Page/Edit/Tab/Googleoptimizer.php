<?php
/**
 * Google Optimizer Cms Page Tab
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Block\Adminhtml\Cms\Page\Edit\Tab;

/**
 * @deprecated 2.1.0
 * @since 2.0.0
 */
class Googleoptimizer extends \Magento\GoogleOptimizer\Block\Adminhtml\AbstractTab
{
    /**
     * Get cms page model
     *
     * @return mixed
     * @throws \RuntimeException
     * @since 2.0.0
     */
    protected function _getEntity()
    {
        $entity = $this->_registry->registry('cms_page');
        if (!$entity) {
            throw new \RuntimeException('Entity is not found in registry.');
        }
        return $entity;
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return __('Page View Optimization');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return __('Page View Optimization');
    }
}
