<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Paypal\Helper\Data as PaypalHelper;

/**
 * Class ExpressConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class ExpressConfigProvider implements ConfigProviderInterface
{
    const IN_CONTEXT_BUTTON_ID = 'paypal-express-in-context-button';

    /**
     * @var ResolverInterface
     * @since 2.0.0
     */
    protected $localeResolver;

    /**
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     * @since 2.0.0
     */
    protected $currentCustomer;

    /**
     * @var PaypalHelper
     * @since 2.0.0
     */
    protected $paypalHelper;

    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $methodCodes = [
        Config::METHOD_WPP_BML,
        Config::METHOD_WPP_PE_EXPRESS,
        Config::METHOD_WPP_EXPRESS,
        Config::METHOD_WPP_PE_BML
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     * @since 2.0.0
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     * @since 2.0.0
     */
    protected $paymentHelper;

    /**
     * @var UrlInterface
     * @since 2.1.0
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ConfigFactory $configFactory
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param PaypalHelper $paypalHelper
     * @param PaymentHelper $paymentHelper
     * @param UrlInterface $urlBuilder
     * @since 2.0.0
     */
    public function __construct(
        ConfigFactory $configFactory,
        ResolverInterface $localeResolver,
        CurrentCustomer $currentCustomer,
        PaypalHelper $paypalHelper,
        PaymentHelper $paymentHelper,
        UrlInterface $urlBuilder
    ) {
        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();
        $this->currentCustomer = $currentCustomer;
        $this->paypalHelper = $paypalHelper;
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getConfig()
    {
        $locale = $this->localeResolver->getLocale();

        $config = [
            'payment' => [
                'paypalExpress' => [
                    'paymentAcceptanceMarkHref' => $this->config->getPaymentMarkWhatIsPaypalUrl(
                        $this->localeResolver
                    ),
                    'paymentAcceptanceMarkSrc' => $this->config->getPaymentMarkImageUrl(
                        $locale
                    ),
                    'isContextCheckout' => false,
                    'inContextConfig' => []
                ]
            ]
        ];

        $isInContext = $this->isInContextCheckout();
        if ($isInContext) {
            $config['payment']['paypalExpress']['isContextCheckout'] = $isInContext;
            $config['payment']['paypalExpress']['inContextConfig'] = [
                'inContextId' => self::IN_CONTEXT_BUTTON_ID,
                'merchantId' => $this->config->getValue('merchant_id'),
                'path' => $this->urlBuilder->getUrl('paypal/express/gettoken', ['_secure' => true]),
                'clientConfig' => [
                    'environment' => ((int) $this->config->getValue('sandbox_flag') ? 'sandbox' : 'production'),
                    'locale' => $locale,
                    'button' => [
                        self::IN_CONTEXT_BUTTON_ID
                    ]
                ],
            ];
        }

        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['paypalExpress']['redirectUrl'][$code] = $this->getMethodRedirectUrl($code);
                $config['payment']['paypalExpress']['billingAgreementCode'][$code] =
                    $this->getBillingAgreementCode($code);
            }
        }

        return $config;
    }

    /**
     * @return bool
     * @since 2.1.0
     */
    protected function isInContextCheckout()
    {
        $this->config->setMethod(Config::METHOD_EXPRESS);

        return (bool)(int) $this->config->getValue('in_context');
    }

    /**
     * Return redirect URL for method
     *
     * @param string $code
     * @return mixed
     * @since 2.0.0
     */
    protected function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getCheckoutRedirectUrl();
    }

    /**
     * Return billing agreement code for method
     *
     * @param string $code
     * @return null|string
     * @since 2.0.0
     */
    protected function getBillingAgreementCode($code)
    {
        $customerId = $this->currentCustomer->getCustomerId();
        $this->config->setMethod($code);
        return $this->paypalHelper->shouldAskToCreateBillingAgreement($this->config, $customerId)
            ? Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT : null;
    }
}
