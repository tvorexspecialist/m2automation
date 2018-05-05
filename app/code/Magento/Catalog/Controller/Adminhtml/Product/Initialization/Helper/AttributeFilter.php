<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

use \Magento\Catalog\Model\Product;

/**
 * Class provides functionality to check and filter data came with product form.
 *
 * The main goal is to avoid database population with empty(null) attribute values.
 */
class AttributeFilter
{
    /**
     * Method provides product data check and its further filtration.
     *
     * Filtration helps us to avoid unnecessary empty product data to be saved.
     * Empty data will be preserved only if user explicitly set it.
     *
     * @param Product $product
     * @param array $productData
     * @param array $useDefaults
     * @return array
     */
    public function prepareProductAttributes(Product $product, array $productData, array $useDefaults)
    {
        foreach ($productData as $attribute => $value) {
            if ($this->attributeShouldNotBeUpdated($product, $useDefaults, $attribute, $value)) {
                unset($productData[$attribute]);
            }
        }
        return $productData;
    }

	/**
	 * @param $product
	 * @param $useDefaults
	 * @param $attribute
	 * @param $value
	 * @return bool
	 */
    protected function attributeShouldNotBeUpdated(Product $product, $useDefaults, $attribute, $value)
    {
	    $considerUseDefaultsAttribute = !isset($useDefaults[$attribute]) || $useDefaults[$attribute] === "1";

	    return ($value === '' && $considerUseDefaultsAttribute && !$product->getData($attribute));
    }
}
