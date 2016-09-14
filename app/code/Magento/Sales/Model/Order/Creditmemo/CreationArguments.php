<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;

/**
 * Class CreationArguments
 */
class CreationArguments implements CreditmemoCreationArgumentsInterface
{
    /**
     * @var float|null
     */
    private $shippingAmount;

    /**
     * @var float|null
     */
    private $adjustmentPositive;

    /**
     * @var float|null
     */
    private $adjustmentNegative;

    /**
     * @var int[]\null
     */
    private $returnToStockItems;

    /**
     * @var \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface
     */
    private $extensionAttributes;

    /**
     * @inheritdoc
     */
    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustmentPositive()
    {
        return $this->adjustmentPositive;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustmentNegative()
    {
        return $this->adjustmentNegative;
    }

    /**
     * @inheritdoc
     */
    public function getReturnToStockItems()
    {
        return $this->returnToStockItems;
    }

    /**
     * @inheritdoc
     */
    public function setShippingAmount($amount)
    {
        $this->shippingAmount = $amount;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setAdjustmentPositive($amount)
    {
        $this->adjustmentPositive = $amount;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setAdjustmentNegative($amount)
    {
        $this->adjustmentNegative = $amount;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setReturnToStockItems($items)
    {
        $this->returnToStockItems = $items;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface $extensionAttributes
    ) {
        $this->extensionAttributes = $extensionAttributes;

        return $this;
    }
}
