<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Assert that url rewrite product in grid.
 */
class AssertUrlRewriteProductInGrid extends AbstractConstraint
{
    /**
     * Curl transport on webapi.
     *
     * @var WebapiDecorator
     */
    private $webApi;

    /**
     * Target path pattern.
     *
     * @var string
     */
    private $targetPathTemplate = 'catalog/product/view/id/%s/category/%s';

    /**
     * Assert that url rewrite product in grid.
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param WebapiDecorator $webApi
     * @param FixtureInterface $product
     * @param Category $category
     * @return void
     */
    public function processAssert(
        UrlRewriteIndex $urlRewriteIndex,
        WebapiDecorator $webApi,
        FixtureInterface $product,
        Category $category = null
    ) {
        $this->webApi = $webApi;
        $urlRewriteIndex->open();
        $categories = $product->getDataFieldConfig('category_ids')['source']->getCategories();
        $rootCategoryArray = [];
        foreach ($categories as $category) {
            $parentName = $category->getDataFieldConfig('parent_id')['source']->getParentCategory()->getName();
            $rootCategoryArray[$parentName] = strtolower($category->getName());
        }

        $stores = $product->getDataFieldConfig('website_ids')['source']->getStores();
        foreach ($stores as $store) {
            $rootCategoryName = $store->getDataFieldConfig('group_id')['source']
                ->getStoreGroup()
                ->getDataFieldConfig('root_category_id')['source']
                ->getCategory()
                ->getName();

            $storeName = $store->getName();
            $filters = [
                [
                    'request_path' => $product->getUrlKey() . '.html',
                    'store_id' => $storeName
                ],
                [
                    'request_path' => $rootCategoryArray[$rootCategoryName] . '.html',
                    'store_id' => $storeName
                ],
                [
                    'request_path' => $rootCategoryArray[$rootCategoryName] . '/' . $product->getUrlKey() . '.html',
                    'target_path' => $this->getTargetPath($product, $category),
                    'store_id' => $storeName
                ],
            ];
            foreach ($filters as $filter) {
                \PHPUnit_Framework_Assert::assertTrue(
                    $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter, true, false),
                    'URL Rewrite with request path \'' . $filter['request_path'] . '\' is absent in grid.'
                );

            }
        }
    }

    /**
     * Get target path.
     *
     * @param FixtureInterface $product
     * @param FixtureInterface|null $category
     * @return string
     */
    private function getTargetPath(FixtureInterface $product, FixtureInterface $category = null)
    {
        $productId = $product->getId()
            ? $product->getId()
            : $this->retrieveProductBySku($product->getSku())['id'];
        $categoryId = $product->hasData('category_ids')
            ? $this->getCategoryId($product)
            : ($category ? $category->getId() : '');
        return sprintf($this->targetPathTemplate, $productId, $categoryId);
    }

    /**
     * Get category id by product.
     *
     * @param FixtureInterface $product
     * @return int
     */
    private function getCategoryId(FixtureInterface $product)
    {
        $productSku = $product->getSku();
        $categoryId = $product->getDataFieldConfig('category_ids')['source']->getCategories()[0]->getId();
        $categoryId = $categoryId
            ? $categoryId
            : $this->retrieveProductBySku($productSku)['extension_attributes']['category_links'][0]['category_id'];
        return $categoryId;
    }

    /**
     * Retrieve product by sku.
     *
     * @param string $sku
     * @return mixed
     */
    public function retrieveProductBySku($sku)
    {
        $url = $_ENV['app_frontend_url'] . 'rest/all/V1/products/' . $sku;
        $this->webApi->write($url, [], WebapiDecorator::GET);
        $response = json_decode($this->webApi->read(), true);
        $this->webApi->close();
        return $response;
    }

    /**
     * URL rewrite product present in grid.
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Rewrite is present in grid.';
    }
}
