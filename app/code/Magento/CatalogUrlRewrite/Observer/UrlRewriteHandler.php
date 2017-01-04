<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryBasedProductRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlRewritesSetFactory;

class UrlRewriteHandler
{
    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider */
    protected $childrenCategoriesProvider;

    /** @var CategoryUrlRewriteGenerator */
    protected $categoryUrlRewriteGenerator;

    /** @var ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /** @var UrlPersistInterface */
    protected $urlPersist;

    /** @var array */
    protected $isSkippedProduct;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    protected $productCollectionFactory;

    /**
     * @var CategoryBasedProductRewriteGenerator
     */
    private $categoryBasedProductRewriteGenerator;

    /** @var \Magento\UrlRewrite\Model\UrlRewritesSet */
    private $urlRewritesSetPrototype;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\UrlRewrite\Model\UrlRewritesSetFactory|null $urlRewritesSetFactory
     */
    public function __construct(
        \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        UrlRewritesSetFactory $urlRewritesSetFactory = null
    ) {
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productCollectionFactory = $productCollectionFactory;
        $urlRewritesSetFactory = $urlRewritesSetFactory ?: ObjectManager::getInstance()
            ->get(UrlRewritesSetFactory::class);
        $this->urlRewritesSetPrototype = $urlRewritesSetFactory->create();
    }

    /**
     * Generate url rewrites for products assigned to category
     *
     * @param Category $category
     * @return array
     */
    public function generateProductUrlRewrites(Category $category)
    {
        $urlRewritesSet = clone $this->urlRewritesSetPrototype;
        $this->isSkippedProduct = [];
        $saveRewriteHistory = $category->getData('save_rewrites_history');
        $storeId = $category->getStoreId();
        if ($category->getAffectedProductIds()) {
            $this->isSkippedProduct = $category->getAffectedProductIds();
            $collection = $this->productCollectionFactory->create()
                ->setStoreId($storeId)
                ->addIdFilter($category->getAffectedProductIds())
                ->addAttributeToSelect('visibility')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path');
            foreach ($collection as $product) {
                $product->setStoreId($storeId);
                $product->setData('save_rewrites_history', $saveRewriteHistory);
                $urlRewritesSet->merge(
                    $this->productUrlRewriteGenerator->generate($product, $category->getEntityId())
                );
            }
        } else {
            $urlRewritesSet->merge(
                $this->getCategoryProductsUrlRewrites(
                    $category,
                    $storeId,
                    $saveRewriteHistory,
                    $category->getEntityId()
                )
            );
        }
        foreach ($this->childrenCategoriesProvider->getChildren($category, true) as $childCategory) {
            $urlRewritesSet->merge(
                $this->getCategoryProductsUrlRewrites(
                    $childCategory,
                    $storeId,
                    $saveRewriteHistory,
                    $category->getEntityId()
                )
            );
        }

        return $urlRewritesSet->getData();
    }

    /**
     * @param Category $category
     * @param int $storeId
     * @param bool $saveRewriteHistory
     * @param int|null $rootCategoryId
     * @return UrlRewrite[]
     */
    public function getCategoryProductsUrlRewrites(
        Category $category,
        $storeId,
        $saveRewriteHistory,
        $rootCategoryId = null
    ) {
        $urlRewritesSet = clone $this->urlRewritesSetPrototype;
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $category->getProductCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');
        foreach ($productCollection as $product) {
            if (in_array($product->getId(), $this->isSkippedProduct)) {
                continue;
            }
            $this->isSkippedProduct[] = $product->getId();
            $product->setStoreId($storeId);
            $product->setData('save_rewrites_history', $saveRewriteHistory);
            $urlRewritesSet->merge(
                $this->getCategoryBasedProductRewriteGenerator()->generate($product, $category, $rootCategoryId)
            );
        }

        return $urlRewritesSet->getData();
    }

    /**
     * Retrieve generator, which use single category for different products
     *
     * @deprecated
     * @return CategoryBasedProductRewriteGenerator|mixed
     */
    private function getCategoryBasedProductRewriteGenerator()
    {
        if (!$this->categoryBasedProductRewriteGenerator) {
            $this->categoryBasedProductRewriteGenerator = ObjectManager::getInstance()
                ->get(CategoryBasedProductRewriteGenerator::class);
        }

        return $this->categoryBasedProductRewriteGenerator;
    }

    /**
     * @param Category $category
     * @return void
     */
    public function deleteCategoryRewritesForChildren(Category $category)
    {
        $categoryIds = $this->childrenCategoriesProvider->getChildrenIds($category, true);
        $categoryIds[] = $category->getId();
        foreach ($categoryIds as $categoryId) {
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::ENTITY_ID => $categoryId,
                    UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::METADATA => serialize(['category_id' => $categoryId]),
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
        }
    }
}
