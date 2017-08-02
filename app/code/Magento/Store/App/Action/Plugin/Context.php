<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\App\Action\Plugin;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Phrase;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\RequestInterface;

/**
 * Class ContextPlugin
 * @since 2.0.0
 */
class Context
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     * @since 2.0.0
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 2.0.0
     */
    protected $httpContext;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var StoreCookieManagerInterface
     * @since 2.0.0
     */
    protected $storeCookieManager;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param StoreCookieManagerInterface $storeCookieManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StoreCookieManagerInterface $storeCookieManager
    ) {
        $this->session      = $session;
        $this->httpContext  = $httpContext;
        $this->storeManager = $storeManager;
        $this->storeCookieManager = $storeCookieManager;
    }

    /**
     * Set store and currency to http context
     *
     * @param AbstractAction $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        /** @var \Magento\Store\Model\Store $defaultStore */
        $defaultStore = $this->storeManager->getWebsite()->getDefaultStore();

        $storeCode = $request->getParam(
            StoreResolverInterface::PARAM_NAME,
            $this->storeCookieManager->getStoreCodeFromCookie()
        );

        if (is_array($storeCode)) {
            if (!isset($storeCode['_data']['code'])) {
                throw new \InvalidArgumentException(new Phrase('Invalid store parameter.'));
            }
            $storeCode = $storeCode['_data']['code'];
        }
        /** @var \Magento\Store\Model\Store $currentStore */
        $currentStore = $storeCode ? $this->storeManager->getStore($storeCode) : $defaultStore;

        $this->httpContext->setValue(
            StoreManagerInterface::CONTEXT_STORE,
            $currentStore->getCode(),
            $this->storeManager->getDefaultStoreView()->getCode()
        );

        $this->httpContext->setValue(
            HttpContext::CONTEXT_CURRENCY,
            $this->session->getCurrencyCode() ?: $currentStore->getDefaultCurrencyCode(),
            $defaultStore->getDefaultCurrencyCode()
        );
    }
}
