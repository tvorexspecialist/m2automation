<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeTreeProviderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class StoreConfigurationProvider
 * Provides config data report
 */
class StoreConfigurationProvider
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string[]
     */
    private $configPaths;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeTreeProviderInterface
     */
    private $scopeTreeProvider;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ScopeTreeProviderInterface $scopeTreeProvider
     * @param $configPaths
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ScopeTreeProviderInterface $scopeTreeProvider,
        $configPaths
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configPaths = $configPaths;
        $this->storeManager = $storeManager;
        $this->scopeTreeProvider = $scopeTreeProvider;
    }

    /**
     * Generates report using config paths from di.xml
     * For each website and store
     * @return \IteratorIterator
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
     * @param string $scope
     * @param int $scope_id
     * @return array
     */
    private function generateReportForScope($scope, $scope_id)
    {
        $report = [];
        foreach ($this->configPaths as $configPath) {
            $report[] = [
                "config_path" => $configPath,
                "scope" => $scope,
                "scope_id" => $scope_id,
                "value" => $this->scopeConfig->getValue(
                    $configPath,
                    $scope,
                    $scope_id
                )
            ];
        }
        return $report;
    }
}
