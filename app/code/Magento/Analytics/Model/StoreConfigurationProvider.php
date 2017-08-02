<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StoreConfigurationProvider
 * Provides config data report
 * @since 2.2.0
 */
class StoreConfigurationProvider
{
    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * @var string[]
     * @since 2.2.0
     */
    private $configPaths;

    /**
     * @var StoreManagerInterface
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param string[] $configPaths
     * @since 2.2.0
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        array $configPaths
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configPaths = $configPaths;
        $this->storeManager = $storeManager;
    }

    /**
     * Generates report using config paths from di.xml
     * For each website and store
     * @return \IteratorIterator
     * @since 2.2.0
     */
    public function getReport()
    {
        $configReport = $this->generateReportForScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);

        /** @var WebsiteInterface $website */
        foreach ($this->storeManager->getWebsites() as $website) {
            $configReport = array_merge(
                $this->generateReportForScope(ScopeInterface::SCOPE_WEBSITES, $website->getId()),
                $configReport
            );
        }

        /** @var StoreInterface $store */
        foreach ($this->storeManager->getStores() as $store) {
            $configReport = array_merge(
                $this->generateReportForScope(ScopeInterface::SCOPE_STORES, $store->getId()),
                $configReport
            );
        }
        return new \IteratorIterator(new \ArrayIterator($configReport));
    }

    /**
     * Creates report from config for scope type and scope id.
     *
     * @param string $scope
     * @param int $scopeId
     * @return array
     * @since 2.2.0
     */
    private function generateReportForScope($scope, $scopeId)
    {
        $report = [];
        foreach ($this->configPaths as $configPath) {
            $report[] = [
                "config_path" => $configPath,
                "scope" => $scope,
                "scope_id" => $scopeId,
                "value" => $this->scopeConfig->getValue(
                    $configPath,
                    $scope,
                    $scopeId
                )
            ];
        }
        return $report;
    }
}
