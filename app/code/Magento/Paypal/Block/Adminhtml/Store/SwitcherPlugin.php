<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\Store;

use Magento\Backend\Block\Store\Switcher as StoreSwitcherBlock;
use Magento\Paypal\Model\Config\StructurePlugin as ConfigStructurePlugin;

/**
 * Plugin for \Magento\Backend\Block\Store\Switcher
 * @since 2.0.0
 */
class SwitcherPlugin
{
    /**
     * Remove country request param from url
     *
     * @param StoreSwitcherBlock $subject
     * @param string $route
     * @param array $params
     * @return array
     * @since 2.2.0
     */
    public function beforeGetUrl(StoreSwitcherBlock $subject, $route = '', $params = [])
    {
        if ($subject->getRequest()->getParam(ConfigStructurePlugin::REQUEST_PARAM_COUNTRY)) {
            $params[ConfigStructurePlugin::REQUEST_PARAM_COUNTRY] = null;
        }

        return [$route, $params];
    }
}
