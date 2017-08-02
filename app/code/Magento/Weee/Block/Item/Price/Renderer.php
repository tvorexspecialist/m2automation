<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Block\Item\Price;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\CreditMemo\Item as CreditMemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;

/**
 * Item price render block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Renderer extends \Magento\Tax\Block\Item\Price\Renderer
{
    /**
     * @var \Magento\Weee\Helper\Data
     * @since 2.0.0
     */
    protected $weeeHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Helper\Data $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Weee\Helper\Data $weeeHelper,
        array $data = []
    ) {
        $this->weeeHelper = $weeeHelper;
        parent::__construct($context, $taxHelper, $priceCurrency, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Whether to display weee details together with price
     *
     * @return bool
     * @since 2.0.0
     */
    public function displayPriceWithWeeeDetails()
    {
        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return false;
        }

        $displayWeeeDetails = $this->weeeHelper->typeOfDisplay(
            [WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_EXCL_DESCR_INCL],
            $this->getZone(),
            $this->getStoreId()
        );
        if (!$displayWeeeDetails) {
            return false;
        }
        if ($this->weeeHelper->getWeeeTaxAppliedAmount($this->getItem()) <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Return the flag whether to include weee in the price
     *
     * @return bool|int
     * @since 2.0.0
     */
    public function getIncludeWeeeFlag()
    {
        $includeWeee = $this->weeeHelper->typeOfDisplay(
            [WeeeDisplayConfig::DISPLAY_INCL_DESCR, WeeeDisplayConfig::DISPLAY_INCL],
            $this->getZone(),
            $this->getStoreId()
        );
        return $includeWeee;
    }

    /**
     * Get display price for unit price including tax. The Weee amount will be added to unit price including tax
     * depending on Weee display setting
     *
     * @return float
     * @since 2.0.0
     */
    public function getUnitDisplayPriceInclTax()
    {
        $priceInclTax = $this->getItem()->getPriceInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $priceInclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $priceInclTax + $this->weeeHelper->getWeeeTaxInclTax($this->getItem());
        }

        return $priceInclTax;
    }

    /**
     * Get base price for unit price including tax. The Weee amount will be added to unit price including tax
     * depending on Weee display setting
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseUnitDisplayPriceInclTax()
    {
        $basePriceInclTax = $this->getItem()->getBasePriceInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $basePriceInclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $basePriceInclTax + $this->weeeHelper->getBaseWeeeTaxInclTax($this->getItem());
        }

        return $basePriceInclTax;
    }

    /**
     * Get display price for row total including tax. The Weee amount will be added to row total including tax
     * depending on Weee display setting
     *
     * @return float
     * @since 2.0.0
     */
    public function getRowDisplayPriceInclTax()
    {
        $rowTotalInclTax = $this->getItem()->getRowTotalInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $rowTotalInclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $rowTotalInclTax + $this->weeeHelper->getRowWeeeTaxInclTax($this->getItem());
        }

        return $rowTotalInclTax;
    }

    /**
     * Get base price for row total including tax. The Weee amount will be added to row total including tax
     * depending on Weee display setting
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseRowDisplayPriceInclTax()
    {
        $baseRowTotalInclTax = $this->getItem()->getBaseRowTotalInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $baseRowTotalInclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $baseRowTotalInclTax + $this->weeeHelper->getBaseRowWeeeTaxInclTax($this->getItem());
        }

        return $baseRowTotalInclTax;
    }

    /**
     * Get display price for unit price excluding tax. The Weee amount will be added to unit price
     * depending on Weee display setting
     *
     * @return float
     * @since 2.0.0
     */
    public function getUnitDisplayPriceExclTax()
    {
        $priceExclTax = $this->getItemDisplayPriceExclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $priceExclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $priceExclTax + $this->weeeHelper->getWeeeTaxAppliedAmount($this->getItem());
        }

        return $priceExclTax;
    }

    /**
     * Get base price for unit price excluding tax. The Weee amount will be added to unit price
     * depending on Weee display setting
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseUnitDisplayPriceExclTax()
    {
        $orderItem = $this->getItem();
        if ($orderItem instanceof InvoiceItem || $orderItem instanceof CreditMemoItem) {
            $orderItem = $orderItem->getOrderItem();
        }

        $qty = $orderItem->getQtyOrdered();
        $basePriceExclTax = $orderItem->getBaseRowTotal() / $qty;

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $basePriceExclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $basePriceExclTax + $this->getItem()->getBaseWeeeTaxAppliedAmount();
        }

        return $basePriceExclTax;
    }

    /**
     * Get display price for row total excluding tax. The Weee amount will be added to row total
     * depending on Weee display setting
     *
     * @return float
     * @since 2.0.0
     */
    public function getRowDisplayPriceExclTax()
    {
        $rowTotalExclTax = $this->getItem()->getRowTotal();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $rowTotalExclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $rowTotalExclTax + $this->weeeHelper->getWeeeTaxAppliedRowAmount($this->getItem());
        }

        return $rowTotalExclTax;
    }

    /**
     * Get base price for row total excluding tax. The Weee amount will be added to row total
     * depending on Weee display setting
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseRowDisplayPriceExclTax()
    {
        $baseRowTotalExclTax = $this->getItem()->getBaseRowTotal();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $baseRowTotalExclTax;
        }

        if ($this->getIncludeWeeeFlag()) {
            return $baseRowTotalExclTax + $this->getItem()->getBaseWeeeTaxAppliedRowAmnt();
        }

        return $baseRowTotalExclTax;
    }

    /**
     * Get final unit display price including tax, this will add Weee amount to unit price include tax
     *
     * @return float
     * @since 2.0.0
     */
    public function getFinalUnitDisplayPriceInclTax()
    {
        $priceInclTax = $this->getItem()->getPriceInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $priceInclTax;
        }

        return $priceInclTax + $this->weeeHelper->getWeeeTaxInclTax($this->getItem());
    }

    /**
     * Get base final unit display price including tax, this will add Weee amount to unit price include tax
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseFinalUnitDisplayPriceInclTax()
    {
        $basePriceInclTax = $this->getItem()->getBasePriceInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $basePriceInclTax;
        }

        return $basePriceInclTax + $this->weeeHelper->getBaseWeeeTaxInclTax($this->getItem());
    }

    /**
     * Get final row display price including tax, this will add weee amount to rowTotalInclTax
     *
     * @return float
     * @since 2.0.0
     */
    public function getFinalRowDisplayPriceInclTax()
    {
        $rowTotalInclTax = $this->getItem()->getRowTotalInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $rowTotalInclTax;
        }

        return $rowTotalInclTax + $this->weeeHelper->getRowWeeeTaxInclTax($this->getItem());
    }

    /**
     * Get base final row display price including tax, this will add weee amount to rowTotalInclTax
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseFinalRowDisplayPriceInclTax()
    {
        $baseRowTotalInclTax = $this->getItem()->getBaseRowTotalInclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $baseRowTotalInclTax;
        }

        return $baseRowTotalInclTax + $this->weeeHelper->getBaseRowWeeeTaxInclTax($this->getItem());
    }

    /**
     * Get final unit display price excluding tax
     *
     * @return float
     * @since 2.0.0
     */
    public function getFinalUnitDisplayPriceExclTax()
    {
        $priceExclTax = $this->getItemDisplayPriceExclTax();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $priceExclTax;
        }

        return $priceExclTax + $this->weeeHelper->getWeeeTaxAppliedAmount($this->getItem());
    }

    /**
     * Get base final unit display price excluding tax
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseFinalUnitDisplayPriceExclTax()
    {
        $orderItem = $this->getItem();
        if ($orderItem instanceof InvoiceItem || $orderItem instanceof CreditMemoItem) {
            $orderItem = $orderItem->getOrderItem();
        }

        $qty = $orderItem->getQtyOrdered();
        $basePriceExclTax = $orderItem->getBaseRowTotal() / $qty;

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $basePriceExclTax;
        }

        return $basePriceExclTax + $this->getItem()->getBaseWeeeTaxAppliedAmount();
    }

    /**
     * Get final row display price excluding tax, this will add Weee amount to rowTotal
     *
     * @return float
     * @since 2.0.0
     */
    public function getFinalRowDisplayPriceExclTax()
    {
        $rowTotalExclTax = $this->getItem()->getRowTotal();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $rowTotalExclTax;
        }

        return $rowTotalExclTax + $this->weeeHelper->getWeeeTaxAppliedRowAmount($this->getItem());
    }

    /**
     * Get base final row display price excluding tax, this will add Weee amount to rowTotal
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseFinalRowDisplayPriceExclTax()
    {
        $baseRowTotalExclTax = $this->getItem()->getBaseRowTotal();

        if (!$this->weeeHelper->isEnabled($this->getStoreId())) {
            return $baseRowTotalExclTax;
        }

        return $baseRowTotalExclTax + $this->getItem()->getBaseWeeeTaxAppliedRowAmnt();
    }

    /**
     * Whether to display final price that include Weee amounts
     *
     * @return bool
     * @since 2.0.0
     */
    public function displayFinalPrice()
    {
        $flag = $this->weeeHelper->typeOfDisplay(
            WeeeDisplayConfig::DISPLAY_EXCL_DESCR_INCL,
            $this->getZone(),
            $this->getStoreId()
        );

        if (!$flag) {
            return false;
        }

        if ($this->weeeHelper->getWeeeTaxAppliedAmount($this->getItem()) <= 0) {
            return false;
        }
        return true;
    }

    /**
     * Return the total amount minus discount
     *
     * @param OrderItem|InvoiceItem|CreditMemoItem $item
     * @return mixed
     * @since 2.0.0
     */
    public function getTotalAmount($item)
    {
        $totalAmount = $item->getRowTotal()
            - $item->getDiscountAmount()
            + $item->getTaxAmount()
            + $item->getDiscountTaxCompensationAmount()
            + $this->weeeHelper->getRowWeeeTaxInclTax($item);

        return $totalAmount;
    }

    /**
     * Return the total amount minus discount
     *
     * @param OrderItem|InvoiceItem|CreditMemoItem $item
     * @return mixed
     * @since 2.0.0
     */
    public function getBaseTotalAmount($item)
    {
        $totalAmount = $item->getBaseRowTotal()
            - $item->getBaseDiscountAmount()
            + $item->getBaseTaxAmount()
            + $item->getBaseDiscountTaxCompensationAmount()
            + $this->weeeHelper->getBaseRowWeeeTaxInclTax($item);

        return $totalAmount;
    }
}
