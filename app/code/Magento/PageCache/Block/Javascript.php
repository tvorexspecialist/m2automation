<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Block;

/**
 * Class Form
 */
class Javascript extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve script options encoded to json
     *
     * @return string
     */
    public function getScriptOptions()
    {
        $params = [
            'url' => $this->getUrl(
                'page_cache/block/render/',
                [
                    '_current' => true,
                    '_secure' => $this->templateContext->getRequest()->isSecure()
                ]
            ),
            'handles' => $this->_layout->getUpdate()->getHandles(),
            'originalRequest' => [
                'route'      => $this->getRequest()->getRouteName(),
                'controller' => $this->getRequest()->getControllerName(),
                'action'     => $this->getRequest()->getActionName(),
                'uri'        => $this->getRequest()->getRequestUri(),
            ],
            'versionCookieName' => \Magento\Framework\App\PageCache\Version::COOKIE_NAME
        ];
        return json_encode($params);
    }
}
