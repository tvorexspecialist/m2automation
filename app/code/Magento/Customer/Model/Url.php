<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Customer url model
 * @since 2.0.0
 */
class Url
{
    /**
     * Route for customer account login page
     */
    const ROUTE_ACCOUNT_LOGIN = 'customer/account/login';

    /**
     * Config name for Redirect Customer to Account Dashboard after Logging in setting
     */
    const XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD = 'customer/startup/redirect_dashboard';

    /**
     * Query param name for last url visited
     */
    const REFERER_QUERY_PARAM_NAME = 'referer';

    /**
     * @var UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * @var RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var EncoderInterface
     * @since 2.0.0
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     * @since 2.2.0
     */
    private $urlDecoder;

    /**
     * @var \Magento\Framework\Url\HostChecker
     * @since 2.2.0
     */
    private $hostChecker;

    /**
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param EncoderInterface $urlEncoder
     * @param \Magento\Framework\Url\DecoderInterface|null $urlDecoder
     * @param \Magento\Framework\Url\HostChecker|null $hostChecker
     * @since 2.0.0
     */
    public function __construct(
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder = null,
        \Magento\Framework\Url\HostChecker $hostChecker = null
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Url\DecoderInterface::class);
        $this->hostChecker = $hostChecker ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Url\HostChecker::class);
    }

    /**
     * Retrieve customer login url
     *
     * @return string
     * @since 2.0.0
     */
    public function getLoginUrl()
    {
        return $this->urlBuilder->getUrl(self::ROUTE_ACCOUNT_LOGIN, $this->getLoginUrlParams());
    }

    /**
     * Retrieve parameters of customer login url
     *
     * @return array
     * @since 2.0.0
     */
    public function getLoginUrlParams()
    {
        $params = [];
        $referer = $this->getRequestReferrer();
        if (!$referer
            && !$this->scopeConfig->isSetFlag(
                self::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
                ScopeInterface::SCOPE_STORE
            )
            && !$this->customerSession->getNoReferer()
        ) {
            $referer = $this->urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
            $referer = $this->urlEncoder->encode($referer);
        }

        if ($referer) {
            $params = [self::REFERER_QUERY_PARAM_NAME => $referer];
        }

        return $params;
    }

    /**
     * Retrieve customer login POST URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getLoginPostUrl()
    {
        $params = [];
        $referer = $this->getRequestReferrer();
        if ($referer) {
            $params = [
                self::REFERER_QUERY_PARAM_NAME => $referer,
            ];
        }
        return $this->urlBuilder->getUrl('customer/account/loginPost', $params);
    }

    /**
     * Retrieve customer logout url
     *
     * @return string
     * @since 2.0.0
     */
    public function getLogoutUrl()
    {
        return $this->urlBuilder->getUrl('customer/account/logout');
    }

    /**
     * Retrieve customer dashboard url
     *
     * @return string
     * @since 2.0.0
     */
    public function getDashboardUrl()
    {
        return $this->urlBuilder->getUrl('customer/account');
    }

    /**
     * Retrieve customer account page url
     *
     * @return string
     * @since 2.0.0
     */
    public function getAccountUrl()
    {
        return $this->urlBuilder->getUrl('customer/account');
    }

    /**
     * Retrieve customer register form url
     *
     * @return string
     * @since 2.0.0
     */
    public function getRegisterUrl()
    {
        return $this->urlBuilder->getUrl('customer/account/create');
    }

    /**
     * Retrieve customer register form post url
     *
     * @return string
     * @since 2.0.0
     */
    public function getRegisterPostUrl()
    {
        return $this->urlBuilder->getUrl('customer/account/createpost');
    }

    /**
     * Retrieve customer account edit form url
     *
     * @return string
     * @since 2.0.0
     */
    public function getEditUrl()
    {
        return $this->urlBuilder->getUrl('customer/account/edit');
    }

    /**
     * Retrieve customer edit POST URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getEditPostUrl()
    {
        return $this->urlBuilder->getUrl('customer/account/editpost');
    }

    /**
     * Retrieve url of forgot password page
     *
     * @return string
     * @since 2.0.0
     */
    public function getForgotPasswordUrl()
    {
        return $this->urlBuilder->getUrl('customer/account/forgotpassword');
    }

    /**
     * Retrieve confirmation URL for Email
     *
     * @param string $email
     * @return string
     * @since 2.0.0
     */
    public function getEmailConfirmationUrl($email = null)
    {
        return $this->urlBuilder->getUrl('customer/account/confirmation', ['email' => $email]);
    }

    /**
     * @return mixed|null
     * @since 2.2.0
     */
    private function getRequestReferrer()
    {
        $referer = $this->request->getParam(self::REFERER_QUERY_PARAM_NAME);
        if ($referer && $this->hostChecker->isOwnOrigin($this->urlDecoder->decode($referer))) {
            return $referer;
        }
        return null;
    }
}
