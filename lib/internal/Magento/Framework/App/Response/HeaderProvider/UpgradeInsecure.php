<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Response\HeaderProvider;

use \Magento\Framework\App\Response\HeaderProvider\AbstractHeader;
use \Magento\Store\Model\Store;

/**
 * Adds an Content-Security-Policy header to HTTP responses.
 */
class UpgradeInsecure extends AbstractHeader
{
    /**
     * Enable Upgrade Insecure Requests config path
     */
    const XML_PATH_ENABLE_UPGRADE_INSECURE = 'web/secure/enable_upgrade_insecure';

    /**
     * Upgrade Insecure Requests Header name
     *
     * @var string
     */
    protected $name = 'Content-Security-Policy';

    /**
     * Upgrade Insecure Requests header value
     *
     * @var string
     */
    protected $value = 'upgrade-insecure-requests';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function canApply()
    {
        return (bool)$this->scopeConfig->isSetFlag(Store::XML_PATH_SECURE_IN_FRONTEND)
            && $this->scopeConfig->isSetFlag(Store::XML_PATH_SECURE_IN_ADMINHTML)
            && $this->scopeConfig->isSetFlag($this::XML_PATH_ENABLE_UPGRADE_INSECURE);
    }
}
