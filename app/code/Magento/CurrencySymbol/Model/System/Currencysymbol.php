<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Model\System;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Custom currency symbol model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 2.0.0
 */
class Currencysymbol
{
    /**
     * Custom currency symbol properties
     *
     * @var array
     * @since 2.0.0
     */
    protected $_symbolsData = [];

    /**
     * Store id
     *
     * @var string|null
     * @since 2.0.0
     */
    protected $_storeId;

    /**
     * Website id
     *
     * @var string|null
     * @since 2.0.0
     */
    protected $_websiteId;

    /**
     * Cache types which should be invalidated
     *
     * @var array
     * @since 2.0.0
     */
    protected $_cacheTypes = [
        \Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER,
        \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER,
        \Magento\Framework\App\Cache\Type\Layout::TYPE_IDENTIFIER,
        \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER,
    ];

    /**
     * Config path to custom currency symbol value
     */
    const XML_PATH_CUSTOM_CURRENCY_SYMBOL = 'currency/options/customsymbol';

    const XML_PATH_ALLOWED_CURRENCIES = \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW;

    /*
     * Separator used in config in allowed currencies list
     */
    const ALLOWED_CURRENCIES_CONFIG_SEPARATOR = ',';

    /**
     * Config currency section
     */
    const CONFIG_SECTION = 'currency';

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     * @since 2.0.0
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Config\Model\Config\Factory
     * @since 2.0.0
     */
    protected $_configFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     * @since 2.0.0
     */
    protected $_systemStore;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     * @since 2.0.0
     */
    protected $_coreConfig;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $coreConfig
     * @param \Magento\Config\Model\Config\Factory $configFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json|null $serializer
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ReinitableConfigInterface $coreConfig,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Json $serializer = null
    ) {
        $this->_coreConfig = $coreConfig;
        $this->_configFactory = $configFactory;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
        $this->_systemStore = $systemStore;
        $this->_eventManager = $eventManager;
        $this->_scopeConfig = $scopeConfig;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Return currency symbol properties array based on config values
     *
     * @return array
     * @since 2.0.0
     */
    public function getCurrencySymbolsData()
    {
        if ($this->_symbolsData) {
            return $this->_symbolsData;
        }

        $this->_symbolsData = [];

        $currentSymbols = $this->_unserializeStoreConfig(self::XML_PATH_CUSTOM_CURRENCY_SYMBOL);

        foreach ($this->getAllowedCurrencies() as $code) {
            $currencies = (new CurrencyBundle())->get($this->localeResolver->getLocale())['Currencies'];
            $symbol = $currencies[$code][0] ?: $code;
            $name = $currencies[$code][1] ?: $code;
            $this->_symbolsData[$code] = ['parentSymbol' => $symbol, 'displayName' => $name];

            if (isset($currentSymbols[$code]) && !empty($currentSymbols[$code])) {
                $this->_symbolsData[$code]['displaySymbol'] = $currentSymbols[$code];
            } else {
                $this->_symbolsData[$code]['displaySymbol'] = $this->_symbolsData[$code]['parentSymbol'];
            }
            $this->_symbolsData[$code]['inherited'] =
                ($this->_symbolsData[$code]['parentSymbol'] == $this->_symbolsData[$code]['displaySymbol']);
        }

        return $this->_symbolsData;
    }

    /**
     * Save currency symbol to config
     *
     * @param  $symbols array
     * @return $this
     * @since 2.0.0
     */
    public function setCurrencySymbolsData($symbols = [])
    {
        foreach ($this->getCurrencySymbolsData() as $code => $values) {
            if (isset($symbols[$code]) && ($symbols[$code] == $values['parentSymbol'] || empty($symbols[$code]))) {
                unset($symbols[$code]);
            }
        }
        $value = [];
        if ($symbols) {
            $value['options']['fields']['customsymbol']['value'] = $this->serializer->serialize($symbols);
        } else {
            $value['options']['fields']['customsymbol']['inherit'] = 1;
        }

        $this->_configFactory->create()
            ->setSection(self::CONFIG_SECTION)
            ->setWebsite(null)
            ->setStore(null)
            ->setGroups($value)
            ->save();

        $this->_eventManager->dispatch(
            'admin_system_config_changed_section_currency_before_reinit',
            ['website' => $this->_websiteId, 'store' => $this->_storeId]
        );

        // reinit configuration
        $this->_coreConfig->reinit();

        $this->clearCache();
        //Reset symbols cache since new data is added
        $this->_symbolsData = [];

        $this->_eventManager->dispatch(
            'admin_system_config_changed_section_currency',
            ['website' => $this->_websiteId, 'store' => $this->_storeId]
        );

        return $this;
    }

    /**
     * Return custom currency symbol by currency code
     *
     * @param string $code
     * @return string|false
     * @since 2.0.0
     */
    public function getCurrencySymbol($code)
    {
        $customSymbols = $this->_unserializeStoreConfig(self::XML_PATH_CUSTOM_CURRENCY_SYMBOL);
        if (array_key_exists($code, $customSymbols)) {
            return $customSymbols[$code];
        }

        return false;
    }

    /**
     * Clear translate cache
     *
     * @return $this
     * @since 2.0.0
     */
    protected function clearCache()
    {
        // clear cache for frontend
        foreach ($this->_cacheTypes as $cacheType) {
            $this->_cacheTypeList->invalidate($cacheType);
        }
        return $this;
    }

    /**
     * Unserialize data from Store Config.
     *
     * @param string $configPath
     * @param int $storeId
     * @return array
     * @since 2.0.0
     */
    protected function _unserializeStoreConfig($configPath, $storeId = null)
    {
        $result = [];
        $configData = (string)$this->_scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($configData) {
            $result = $this->serializer->unserialize($configData);
        }

        return is_array($result) ? $result : [];
    }

    /**
     * Return allowed currencies
     *
     * @return array
     * @since 2.0.0
     */
    protected function getAllowedCurrencies()
    {
        $allowedCurrencies = explode(
            self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR,
            $this->_scopeConfig->getValue(
                self::XML_PATH_ALLOWED_CURRENCIES,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
        );

        $storeModel = $this->_systemStore;
        /** @var \Magento\Store\Model\Website $website */
        foreach ($storeModel->getWebsiteCollection() as $website) {
            $websiteShow = false;
            /** @var \Magento\Store\Model\Group $group */
            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                /** @var \Magento\Store\Model\Store $store */
                foreach ($storeModel->getStoreCollection() as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $websiteSymbols = $website->getConfig(self::XML_PATH_ALLOWED_CURRENCIES);
                        $allowedCurrencies = array_merge(
                            $allowedCurrencies,
                            explode(self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR, $websiteSymbols)
                        );
                    }
                    $storeSymbols = $this->_scopeConfig->getValue(
                        self::XML_PATH_ALLOWED_CURRENCIES,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store
                    );
                    $allowedCurrencies = array_merge(
                        $allowedCurrencies,
                        explode(self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR, $storeSymbols)
                    );
                }
            }
        }
        return array_unique($allowedCurrencies);
    }
}
