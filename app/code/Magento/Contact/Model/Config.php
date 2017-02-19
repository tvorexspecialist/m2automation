<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Model;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Contact module configuration
 */
class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function emailTemplate()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function emailSender()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function emailRecipient()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EMAIL_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
