<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\FrontController\Plugin;

use \Magento\Store\Model\StoreResolver\ReaderList;
use \Magento\Store\Model\ScopeInterface;

/**
 * Plugin to set default store for admin area.
 * @since 2.1.0
 */
class DefaultStore
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.1.0
     */
    protected $storeManager;

    /**
     * @var ReaderList
     * @since 2.1.0
     */
    protected $readerList;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $runMode;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $scopeCode;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ReaderList $readerList
     * @param string $runMode
     * @param null $scopeCode
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ReaderList $readerList,
        $runMode = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        $this->runMode = $scopeCode ? $runMode : ScopeInterface::SCOPE_WEBSITE;
        $this->scopeCode = $scopeCode;
        $this->readerList = $readerList;
        $this->storeManager = $storeManager;
    }

    /**
     * Set current store for admin area
     *
     * @param \Magento\Framework\App\FrontController $subject
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function beforeDispatch(
        \Magento\Framework\App\FrontController $subject,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $reader = $this->readerList->getReader($this->runMode);
        $defaultStoreId = $reader->getDefaultStoreId($this->scopeCode);
        $this->storeManager->setCurrentStore($defaultStoreId);
    }
}
