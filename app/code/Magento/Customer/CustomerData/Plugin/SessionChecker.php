<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData\Plugin;

use Magento\Framework\Session\SessionManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

/**
 * Class \Magento\Customer\CustomerData\Plugin\SessionChecker
 *
 * @since 2.1.0
 */
class SessionChecker
{
    /**
     * @var PhpCookieManager
     * @since 2.1.0
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     * @since 2.1.0
     */
    private $cookieMetadataFactory;

    /**
     * @param PhpCookieManager $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @since 2.1.0
     */
    public function __construct(
        PhpCookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * Delete frontend session cookie if customer session is expired
     *
     * @param SessionManager $sessionManager
     * @return void
     * @since 2.1.0
     */
    public function beforeStart(SessionManager $sessionManager)
    {
        if (!$this->cookieManager->getCookie($sessionManager->getName())
            && $this->cookieManager->getCookie('mage-cache-sessid')
        ) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }
}
