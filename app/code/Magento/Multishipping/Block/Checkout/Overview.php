<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Block\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Multishipping checkout overview information
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Overview extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';

    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @since 2.0.0
     */
    protected $_multishipping;

    /**
     * @var \Magento\Tax\Helper\Data
     * @since 2.0.0
     */
    protected $_taxHelper;

    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     * @since 2.0.0
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsReader
     * @since 2.0.0
     */
    protected $totalsReader;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param \Magento\Quote\Model\Quote\TotalsReader $totalsReader
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Tax\Helper\Data $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Model\Quote\TotalsReader $totalsReader,
        array $data = []
    ) {
        $this->_taxHelper = $taxHelper;
        $this->_multishipping = $multishipping;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->totalsCollector = $totalsCollector;
        $this->totalsReader = $totalsReader;
    }

    /**
     * Initialize default item renderer
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(
            __('Review Order - %1', $this->pageConfig->getTitle()->getDefault())
        );
        return parent::_prepareLayout();
    }

    /**
     * Get multishipping checkout model
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @since 2.0.0
     */
    public function getCheckout()
    {
        return $this->_multishipping;
    }

    /**
     * @return Address
     * @since 2.0.0
     */
    public function getBillingAddress()
    {
        return $this->getCheckout()->getQuote()->getBillingAddress();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getPaymentHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Get object with payment info posted data
     *
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function getPayment()
    {
        if (!$this->hasData('payment')) {
            $payment = new \Magento\Framework\DataObject($this->getRequest()->getPost('payment'));
            $this->setData('payment', $payment);
        }
        return $this->_getData('payment');
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getShippingAddresses()
    {
        return $this->getCheckout()->getQuote()->getAllShippingAddresses();
    }

    /**
     * @return int|mixed
     * @since 2.0.0
     */
    public function getShippingAddressCount()
    {
        $count = $this->getData('shipping_address_count');
        if ($count === null) {
            $count = count($this->getShippingAddresses());
            $this->setData('shipping_address_count', $count);
        }
        return $count;
    }

    /**
     * @param Address $address
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getShippingAddressRate($address)
    {
        $rate = $address->getShippingRateByCode($address->getShippingMethod());
        if ($rate) {
            return $rate;
        }
        return false;
    }

    /**
     * @param Address $address
     * @return mixed
     * @since 2.0.0
     */
    public function getShippingPriceInclTax($address)
    {
        $exclTax = $address->getShippingAmount();
        $taxAmount = $address->getShippingTaxAmount();
        return $this->formatPrice($exclTax + $taxAmount);
    }

    /**
     * @param Address $address
     * @return mixed
     * @since 2.0.0
     */
    public function getShippingPriceExclTax($address)
    {
        return $this->formatPrice($address->getShippingAmount());
    }

    /**
     * @param float $price
     * @return mixed
     *
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function formatPrice($price)
    {
        return $this->priceCurrency->format(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        );
    }

    /**
     * @param Address $address
     * @return mixed
     * @since 2.0.0
     */
    public function getShippingAddressItems($address)
    {
        return $address->getAllVisibleItems();
    }

    /**
     * @param Address $address
     * @return mixed
     * @since 2.0.0
     */
    public function getShippingAddressTotals($address)
    {
        $totals = $address->getTotals();
        foreach ($totals as $total) {
            if ($total->getCode() == 'grand_total') {
                if ($address->getAddressType() == Address::TYPE_BILLING) {
                    $total->setTitle(__('Total'));
                } else {
                    $total->setTitle(__('Total for this address'));
                }
            }
        }
        return $totals;
    }

    /**
     * @return float
     * @since 2.0.0
     */
    public function getTotal()
    {
        return $this->getCheckout()->getQuote()->getGrandTotal();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAddressesEditUrl()
    {
        return $this->getUrl('*/*/backtoaddresses');
    }

    /**
     * @param Address $address
     * @return string
     * @since 2.0.0
     */
    public function getEditShippingAddressUrl($address)
    {
        return $this->getUrl('*/checkout_address/editShipping', ['id' => $address->getCustomerAddressId()]);
    }

    /**
     * @param Address $address
     * @return string
     * @since 2.0.0
     */
    public function getEditBillingAddressUrl($address)
    {
        return $this->getUrl('*/checkout_address/editBilling', ['id' => $address->getCustomerAddressId()]);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getEditShippingUrl()
    {
        return $this->getUrl('*/*/backtoshipping');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/overviewPost');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getEditBillingUrl()
    {
        return $this->getUrl('*/*/backtobilling');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/backtobilling');
    }

    /**
     * Retrieve virtual product edit url
     *
     * @return string
     * @since 2.0.0
     */
    public function getVirtualProductEditUrl()
    {
        return $this->getUrl('checkout/cart');
    }

    /**
     * Retrieve virtual product collection array
     *
     * @return array
     * @since 2.0.0
     */
    public function getVirtualItems()
    {
        $items = [];
        foreach ($this->getBillingAddress()->getItemsCollection() as $_item) {
            if ($_item->isDeleted()) {
                continue;
            }
            if ($_item->getProduct()->getIsVirtual() && !$_item->getParentItemId()) {
                $items[] = $_item;
            }
        }
        return $items;
    }

    /**
     * Retrieve quote
     *
     * @return \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getBillinAddressTotals()
    {
        $address = $this->getQuote()->getBillingAddress();
        return $this->getShippingAddressTotals($address);
    }

    /**
     * @param mixed $totals
     * @param null $colspan
     * @return string
     * @since 2.0.0
     */
    public function renderTotals($totals, $colspan = null)
    {
        if ($colspan === null) {
            $colspan = 3;
        }
        $totals = $this->getChildBlock(
            'totals'
        )->setTotals(
            $totals
        )->renderTotals(
            '',
            $colspan
        ) . $this->getChildBlock(
            'totals'
        )->setTotals(
            $totals
        )->renderTotals(
            'footer',
            $colspan
        );
        return $totals;
    }

    /**
     * Return row-level item html
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     * @since 2.0.0
     */
    public function getRowItemHtml(\Magento\Framework\DataObject $item)
    {
        $type = $this->_getItemType($item);
        $renderer = $this->_getRowItemRenderer($type)->setItem($item);
        $this->_prepareItem($renderer);
        return $renderer->toHtml();
    }

    /**
     * Retrieve renderer block for row-level item output
     *
     * @param string $type
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    protected function _getRowItemRenderer($type)
    {
        $renderer = $this->getItemRenderer($type);
        if ($renderer !== $this->getItemRenderer(self::DEFAULT_TYPE)) {
            $renderer->setTemplate($this->getRowRendererTemplate());
        }
        return $renderer;
    }
}
