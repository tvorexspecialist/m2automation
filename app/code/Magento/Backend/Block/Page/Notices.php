<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml header notices block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Block\Page;

/**
 * @api
 * @since 2.0.0
 */
class Notices extends \Magento\Backend\Block\Template
{
    /**
     * Check if noscript notice should be displayed
     *
     * @return boolean
     * @since 2.0.0
     */
    public function displayNoscriptNotice()
    {
        return $this->_scopeConfig->getValue(
            'web/browser_capabilities/javascript',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if demo store notice should be displayed
     *
     * @return boolean
     * @since 2.0.0
     */
    public function displayDemoNotice()
    {
        return $this->_scopeConfig->getValue(
            'design/head/demonotice',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
