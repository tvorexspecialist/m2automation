<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Bundle\Api\Data\BundleOptionInterfaceFactory;
use Magento\Quote\Api\Data as QuoteApi;

class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @var QuoteApi\ProductOptionExtensionFactory
     */
    protected $productOptionExtensionFactory;

    /**
     * @var BundleOptionInterfaceFactory
     */
    protected $bundleOptionFactory;

    /**
     * @var QuoteApi\ProductOptionInterfaceFactory
     */
    protected $productOptionFactory;

    /**
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param QuoteApi\ProductOptionExtensionFactory $productOptionExtensionFactory
     * @param BundleOptionInterfaceFactory $bundleOptionFactory
     * @param QuoteApi\ProductOptionInterfaceFactory $productOptionFactory
     */
    public function __construct(
        \Magento\Framework\DataObject\Factory $objectFactory,
        QuoteApi\ProductOptionExtensionFactory $productOptionExtensionFactory,
        BundleOptionInterfaceFactory $bundleOptionFactory,
        QuoteApi\ProductOptionInterfaceFactory $productOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->productOptionExtensionFactory = $productOptionExtensionFactory;
        $this->bundleOptionFactory = $bundleOptionFactory;
        $this->productOptionFactory = $productOptionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        if ($cartItem->getProductOption() && $cartItem->getProductOption()->getExtensionAttributes()) {
            $options = $cartItem->getProductOption()->getExtensionAttributes()->getBundleOptions();
            if (is_array($options)) {
                $requestData = [];
                foreach ($options as $option) {
                    /** @var \Magento\Bundle\Api\Data\BundleOptionInterface $option */
                    foreach ($option->getOptionSelections() as $selection) {
                        $requestData['bundle_option'][$option->getOptionId()][] = $selection;
                        $requestData['bundle_option_qty'][$option->getOptionId()] = $option->getOptionQty();
                    }
                }
                return $this->objectFactory->create($requestData);
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processProductOptions(CartItemInterface $cartItem)
    {
        if ($cartItem->getProductType() !== \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            return $cartItem;
        }
        $productOptions = [];
        $bundleOptions = $cartItem->getBuyRequest()->getBundleOption();
        $bundleOptionsQty = $cartItem->getBuyRequest()->getBundleOptionQty();
        foreach ($bundleOptions as $optionId => $optionSelections) {
            if (empty($optionSelections)) {
                continue;
            }
            $optionSelections = is_array($optionSelections) ? $optionSelections : [$optionSelections];
            $optionQty = isset($bundleOptionsQty[$optionId]) ? $bundleOptionsQty[$optionId] : 1;

            /** @var \Magento\Bundle\Api\Data\BundleOptionInterface $productOption */
            $productOption = $this->bundleOptionFactory->create();
            $productOption->setOptionId($optionId);
            $productOption->setOptionSelections($optionSelections);
            $productOption->setOptionQty($optionQty);
            $productOptions[] = $productOption;
        }

        $extension = $this->productOptionExtensionFactory->create()->setBundleOptions($productOptions);
        if (!$cartItem->getProductOption()) {
            $cartItem->setProductOption($this->productOptionFactory->create());
        }
        $cartItem->getProductOption()->setExtensionAttributes($extension);
        return $cartItem;
    }
}
