<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Controller\Adminhtml;

/**
 * XML sitemap controller
 * @since 2.0.0
 */
abstract class Sitemap extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sitemap::sitemap';

    /**
     * Init actions
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Sitemap::catalog_sitemap'
        )->_addBreadcrumb(
            __('Catalog'),
            __('Catalog')
        )->_addBreadcrumb(
            __('XML Sitemap'),
            __('XML Sitemap')
        );
        return $this;
    }
}
