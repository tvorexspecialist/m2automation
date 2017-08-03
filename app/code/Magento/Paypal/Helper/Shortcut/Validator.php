<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Helper\Shortcut;

/**
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @since 2.0.0
 */
class Validator implements ValidatorInterface
{
    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     * @since 2.0.0
     */
    private $_paypalConfigFactory;

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    private $_registry;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     * @since 2.0.0
     */
    private $_productTypeConfig;

    /**
     * @var \Magento\Payment\Helper\Data
     * @since 2.0.0
     */
    private $_paymentData;

    /**
     * @param \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Payment\Helper\Data $paymentData
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Payment\Helper\Data $paymentData
    ) {
        $this->_paypalConfigFactory = $paypalConfigFactory;
        $this->_registry = $registry;
        $this->_productTypeConfig = $productTypeConfig;
        $this->_paymentData = $paymentData;
    }

    /**
     * Validates shortcut
     *
     * @param string $code
     * @param bool $isInCatalog
     * @return bool
     * @since 2.0.0
     */
    public function validate($code, $isInCatalog)
    {
        return $this->isContextAvailable($code, $isInCatalog)
            && $this->isPriceOrSetAvailable($isInCatalog)
            && $this->isMethodAvailable($code);
    }

    /**
     * Checks visibility of context (cart or product page)
     *
     * @param string $paymentCode Payment method code
     * @param bool $isInCatalog
     * @return bool
     * @since 2.0.0
     */
    public function isContextAvailable($paymentCode, $isInCatalog)
    {
        /** @var \Magento\Paypal\Model\Config $config */
        $config = $this->_paypalConfigFactory->create();
        $config->setMethod($paymentCode);

        // check visibility on cart or product page
        $context = $isInCatalog ? 'visible_on_product' : 'visible_on_cart';
        if (!$config->getValue($context)) {
            return false;
        }
        return true;
    }

    /**
     * Check is product available depending on final price or type set(configurable)
     *
     * @param bool $isInCatalog
     * @return bool
     * @since 2.0.0
     */
    public function isPriceOrSetAvailable($isInCatalog)
    {
        if ($isInCatalog) {
            // Show PayPal shortcut on a product view page only if product has nonzero price
            /** @var $currentProduct \Magento\Catalog\Model\Product */
            $currentProduct = $this->_registry->registry('current_product');
            if ($currentProduct !== null) {
                $productPrice = (double)$currentProduct->getFinalPrice();
                $typeInstance = $currentProduct->getTypeInstance();
                if (empty($productPrice)
                    && !$this->_productTypeConfig->isProductSet($currentProduct->getTypeId())
                    && !$typeInstance->canConfigure($currentProduct)
                ) {
                    return  false;
                }
            }
        }
        return true;
    }

    /**
     * Checks payment method and quote availability
     *
     * @param string $paymentCode
     * @return bool
     * @since 2.0.0
     */
    public function isMethodAvailable($paymentCode)
    {
        // check payment method availability
        /** @var \Magento\Payment\Model\Method\AbstractMethod $methodInstance */
        $methodInstance = $this->_paymentData->getMethodInstance($paymentCode);
        if (!$methodInstance->isAvailable()) {
            return false;
        }
        return true;
    }
}
