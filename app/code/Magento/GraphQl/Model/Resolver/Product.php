<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\ServiceOutputProcessor;

/**
 * Product field resolver, used for GraphQL request processing.
 */
class Product
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var MediaGalleryEntries
     */
    private $mediaGalleryResolver;

    /**
     * Initialize dependencies
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param MediaGalleryEntries $mediaGalleryResolver
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ServiceOutputProcessor $serviceOutputProcessor,
        MediaGalleryEntries $mediaGalleryResolver
    ) {
        $this->productRepository = $productRepository;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->mediaGalleryResolver = $mediaGalleryResolver;
    }

    /**
     * Resolves product by Sku
     *
     * @param string $sku
     * @return array|null
     */
    public function getProduct(string $sku)
    {
        try {
            $productObject = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            // No error should be thrown, null result should be returned
            return null;
        }
        return $this->processProduct($productObject);
    }

    /**
     * Resolves product by Id
     *
     * @param int $productId
     * @return array|null
     */
    public function getProductById(int $productId)
    {
        try {
            $productObject = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            // No error should be thrown, null result should be returned
            return null;
        }
        return $this->processProduct($productObject);
    }

    /**
     * Retrieve single product data in array format
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $productObject
     * @return array|null
     */
    private function processProduct(\Magento\Catalog\Api\Data\ProductInterface $productObject)
    {
        $product = $this->serviceOutputProcessor->process(
            $productObject,
            ProductRepositoryInterface::class,
            'get'
        );
        $product = array_merge($product, $product['extension_attributes']);
        if (isset($product['custom_attributes'])) {
            $customAttributes = [];
            foreach ($product['custom_attributes'] as $attribute) {
                $isArray = false;
                if (is_array($attribute['value'])) {
                    $isArray = true;
                    foreach ($attribute['value'] as $attributeValue) {
                        if (is_array($attributeValue)) {
                            $customAttributes[$attribute['attribute_code']] = json_encode($attribute['value']);
                            continue;
                        }
                        $customAttributes[$attribute['attribute_code']] = implode(',', $attribute['value']);
                        continue;
                    }
                }
                if ($isArray) {
                    continue;
                }
                $customAttributes[$attribute['attribute_code']] = $attribute['value'];
            }
        }
        $product = array_merge($product, $customAttributes);
        $product = array_merge($product, $product['product_links']);
        $product['media_gallery_entries'] = $this
            ->mediaGalleryResolver->getMediaGalleryEntries($productObject->getSku());

        if (isset($product['configurable_product_links'])) {
            $product['configurable_product_links'] = $this
                ->resolveConfigurableProductLinks($product['configurable_product_links']);
        }

        return $product;
    }

    /**
     * Resolve links for configurable product into simple products
     *
     * @param int[]
     * @return array
     */
    private function resolveConfigurableProductLinks($configurableProductLinks)
    {
        if (empty($configurableProductLinks)) {
            return [];
        }
        $result = [];
        foreach ($configurableProductLinks as $key => $id) {
            $result[$key] = $this->getProductById($id);
        }
        return $result;
    }
}
