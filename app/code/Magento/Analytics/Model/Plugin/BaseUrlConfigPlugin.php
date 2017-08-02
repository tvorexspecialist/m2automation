<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Plugin;

use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Store\Model\Store;

/**
 * Plugin on Base URL config value AfterSave method.
 * @since 2.2.0
 */
class BaseUrlConfigPlugin
{
    /**
     * @var SubscriptionUpdateHandler
     * @since 2.2.0
     */
    private $subscriptionUpdateHandler;

    /**
     * @param SubscriptionUpdateHandler $subscriptionUpdateHandler
     * @since 2.2.0
     */
    public function __construct(
        SubscriptionUpdateHandler $subscriptionUpdateHandler
    ) {
        $this->subscriptionUpdateHandler = $subscriptionUpdateHandler;
    }

    /**
     * Add additional handling after config value was saved.
     *
     * @param Value $subject
     * @param Value $result
     * @return Value
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterAfterSave(
        Value $subject,
        Value $result
    ) {
        if ($this->isPluginApplicable($result)) {
            $this->subscriptionUpdateHandler->processUrlUpdate($result->getOldValue());
        }

        return $result;
    }

    /**
     * @param Value $result
     * @return bool
     * @since 2.2.0
     */
    private function isPluginApplicable(Value $result)
    {
        return $result->isValueChanged()
            && ($result->getPath() === Store::XML_PATH_SECURE_BASE_URL)
            && ($result->getScope() === ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }
}
