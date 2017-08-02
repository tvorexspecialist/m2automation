<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart\Totals;

use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Cart item totals.
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Item extends AbstractExtensibleObject implements TotalsItemInterface
{
    /**
     * Set totals item id
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setItemId($id)
    {
        return $this->setData(self::KEY_ITEM_ID, $id);
    }

    /**
     * Get totals item id
     *
     * @return int Item id
     * @since 2.0.0
     */
    public function getItemId()
    {
        return $this->_get(self::KEY_ITEM_ID);
    }

    /**
     * Returns the item price in quote currency.
     *
     * @return float Item price in quote currency.
     * @since 2.0.0
     */
    public function getPrice()
    {
        return $this->_get(self::KEY_PRICE);
    }

    /**
     * Sets the item price in quote currency.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price)
    {
        return $this->setData(self::KEY_PRICE, $price);
    }

    /**
     * Returns the item price in base currency.
     *
     * @return float Item price in base currency.
     * @since 2.0.0
     */
    public function getBasePrice()
    {
        return $this->_get(self::KEY_BASE_PRICE);
    }

    /**
     * Sets the item price in base currency.
     *
     * @param float $basePrice
     * @return $this
     * @since 2.0.0
     */
    public function setBasePrice($basePrice)
    {
        return $this->setData(self::KEY_BASE_PRICE, $basePrice);
    }

    /**
     * Returns the item quantity.
     *
     * @return int Item quantity.
     * @since 2.0.0
     */
    public function getQty()
    {
        return $this->_get(self::KEY_QTY);
    }

    /**
     * Sets the item quantity.
     *
     * @param int $qty
     * @return $this
     * @since 2.0.0
     */
    public function setQty($qty)
    {
        return $this->setData(self::KEY_QTY, $qty);
    }

    /**
     * Returns the row total in quote currency.
     *
     * @return float Row total in quote currency.
     * @since 2.0.0
     */
    public function getRowTotal()
    {
        return $this->_get(self::KEY_ROW_TOTAL);
    }

    /**
     * Sets the row total in quote currency.
     *
     * @param float $rowTotal
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotal($rowTotal)
    {
        return $this->setData(self::KEY_ROW_TOTAL, $rowTotal);
    }

    /**
     * Returns the row total in base currency.
     *
     * @return float Row total in base currency.
     * @since 2.0.0
     */
    public function getBaseRowTotal()
    {
        return $this->_get(self::KEY_BASE_ROW_TOTAL);
    }

    /**
     * Sets the row total in base currency.
     *
     * @param float $baseRowTotal
     * @return $this
     * @since 2.0.0
     */
    public function setBaseRowTotal($baseRowTotal)
    {
        return $this->setData(self::KEY_BASE_ROW_TOTAL, $baseRowTotal);
    }

    /**
     * Returns the row total with discount in quote currency.
     *
     * @return float|null Row total with discount in quote currency. Otherwise, null.
     * @since 2.0.0
     */
    public function getRowTotalWithDiscount()
    {
        return $this->_get(self::KEY_ROW_TOTAL_WITH_DISCOUNT);
    }

    /**
     * Sets the row total with discount in quote currency.
     *
     * @param float $rowTotalWithDiscount
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotalWithDiscount($rowTotalWithDiscount)
    {
        return $this->setData(self::KEY_ROW_TOTAL_WITH_DISCOUNT, $rowTotalWithDiscount);
    }

    /**
     * Returns the tax amount in quote currency.
     *
     * @return float|null Tax amount in quote currency. Otherwise, null.
     * @since 2.0.0
     */
    public function getTaxAmount()
    {
        return $this->_get(self::KEY_TAX_AMOUNT);
    }

    /**
     * Sets the tax amount in quote currency.
     *
     * @param float $taxAmount
     * @return $this
     * @since 2.0.0
     */
    public function setTaxAmount($taxAmount)
    {
        return $this->setData(self::KEY_TAX_AMOUNT, $taxAmount);
    }

    /**
     * Returns the tax amount in base currency.
     *
     * @return float|null Tax amount in base currency. Otherwise, null.
     * @since 2.0.0
     */
    public function getBaseTaxAmount()
    {
        return $this->_get(self::KEY_BASE_TAX_AMOUNT);
    }

    /**
     * Sets the tax amount in base currency.
     *
     * @param float $baseTaxAmount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseTaxAmount($baseTaxAmount)
    {
        return $this->setData(self::KEY_BASE_TAX_AMOUNT, $baseTaxAmount);
    }

    /**
     * Returns the tax percent.
     *
     * @return int|null Tax percent. Otherwise, null.
     * @since 2.0.0
     */
    public function getTaxPercent()
    {
        return $this->_get(self::KEY_TAX_PERCENT);
    }

    /**
     * Sets the tax percent.
     *
     * @param int $taxPercent
     * @return $this
     * @since 2.0.0
     */
    public function setTaxPercent($taxPercent)
    {
        return $this->setData(self::KEY_TAX_PERCENT, $taxPercent);
    }

    /**
     * Returns the discount amount in quote currency.
     *
     * @return float|null Discount amount in quote currency. Otherwise, null.
     * @since 2.0.0
     */
    public function getDiscountAmount()
    {
        return $this->_get(self::KEY_DISCOUNT_AMOUNT);
    }

    /**
     * Sets the discount amount in quote currency.
     *
     * @param float $discountAmount
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountAmount($discountAmount)
    {
        return $this->setData(self::KEY_DISCOUNT_AMOUNT, $discountAmount);
    }

    /**
     * Returns the discount amount in base currency.
     *
     * @return float|null Discount amount in base currency. Otherwise, null.
     * @since 2.0.0
     */
    public function getBaseDiscountAmount()
    {
        return $this->_get(self::KEY_BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Sets the discount amount in base currency.
     *
     * @param float $baseDiscountAmount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseDiscountAmount($baseDiscountAmount)
    {
        return $this->setData(self::KEY_BASE_DISCOUNT_AMOUNT, $baseDiscountAmount);
    }

    /**
     * Returns the discount percent.
     *
     * @return int|null Discount percent. Otherwise, null.
     * @since 2.0.0
     */
    public function getDiscountPercent()
    {
        return $this->_get(self::KEY_DISCOUNT_PERCENT);
    }

    /**
     * Sets the discount percent.
     *
     * @param int $discountPercent
     * @return $this
     * @since 2.0.0
     */
    public function setDiscountPercent($discountPercent)
    {
        return $this->setData(self::KEY_DISCOUNT_PERCENT, $discountPercent);
    }

    /**
     * Returns the price including tax in quote currency.
     *
     * @return float|null Price including tax in quote currency. Otherwise, null.
     * @since 2.0.0
     */
    public function getPriceInclTax()
    {
        return $this->_get(self::KEY_PRICE_INCL_TAX);
    }

    /**
     * Sets the price including tax in quote currency.
     *
     * @param float $priceInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setPriceInclTax($priceInclTax)
    {
        return $this->setData(self::KEY_PRICE_INCL_TAX, $priceInclTax);
    }

    /**
     * Returns the price including tax in base currency.
     *
     * @return float|null Price including tax in base currency. Otherwise, null.
     * @since 2.0.0
     */
    public function getBasePriceInclTax()
    {
        return $this->_get(self::KEY_BASE_PRICE_INCL_TAX);
    }

    /**
     * Sets the price including tax in base currency.
     *
     * @param float $basePriceInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setBasePriceInclTax($basePriceInclTax)
    {
        return $this->setData(self::KEY_BASE_PRICE_INCL_TAX, $basePriceInclTax);
    }

    /**
     * Returns the row total including tax in quote currency.
     *
     * @return float|null Row total including tax in quote currency. Otherwise, null.
     * @since 2.0.0
     */
    public function getRowTotalInclTax()
    {
        return $this->_get(self::KEY_ROW_TOTAL_INCL_TAX);
    }

    /**
     * Sets the row total including tax in quote currency.
     *
     * @param float $rowTotalInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setRowTotalInclTax($rowTotalInclTax)
    {
        return $this->setData(self::KEY_ROW_TOTAL_INCL_TAX, $rowTotalInclTax);
    }

    /**
     * Returns the row total including tax in base currency.
     *
     * @return float|null Row total including tax in base currency. Otherwise, null.
     * @since 2.0.0
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->_get(self::KEY_BASE_ROW_TOTAL_INCL_TAX);
    }

    /**
     * Sets the row total including tax in base currency.
     *
     * @param float $baseRowTotalInclTax
     * @return $this
     * @since 2.0.0
     */
    public function setBaseRowTotalInclTax($baseRowTotalInclTax)
    {
        return $this->setData(self::KEY_BASE_ROW_TOTAL_INCL_TAX, $baseRowTotalInclTax);
    }

    /**
     * Returns the item options data.
     *
     * @return string[] Item price in quote currency.
     * @since 2.0.0
     */
    public function getOptions()
    {
        return $this->_get(self::KEY_OPTIONS);
    }

    /**
     * Sets the item options data.
     *
     * @param string $options
     * @return $this
     * @since 2.0.0
     */
    public function setOptions($options)
    {
        return $this->setData(self::KEY_OPTIONS, $options);
    }

    /**
     * Returns the item weee tax applied amount in quote currency.
     *
     * @return float Item weee tax applied amount in quote currency.
     * @since 2.0.0
     */
    public function getWeeeTaxAppliedAmount()
    {
        return $this->_get(self::KEY_WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Sets the item weee tax applied amount in quote currency.
     *
     * @param float $weeeTaxAppliedAmount
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxAppliedAmount($weeeTaxAppliedAmount)
    {
        return $this->setData(self::KEY_WEEE_TAX_APPLIED_AMOUNT, $weeeTaxAppliedAmount);
    }

    /**
     * Returns the item weee tax applied in quote currency.
     *
     * @return string[] Item weee tax applied in quote currency.
     * @since 2.0.0
     */
    public function getWeeeTaxApplied()
    {
        return $this->_get(self::KEY_WEEE_TAX_APPLIED);
    }

    /**
     * Sets the item weee tax applied in quote currency.
     *
     * @param string $weeeTaxApplied
     * @return $this
     * @since 2.0.0
     */
    public function setWeeeTaxApplied($weeeTaxApplied)
    {
        return $this->setData(self::KEY_WEEE_TAX_APPLIED, $weeeTaxApplied);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->_get(self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Quote\Api\Data\TotalsItemExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Quote\Api\Data\TotalsItemExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\TotalsItemExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
