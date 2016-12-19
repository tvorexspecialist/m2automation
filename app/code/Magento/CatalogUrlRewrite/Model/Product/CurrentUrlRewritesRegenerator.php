<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\CatalogUrlRewrite\Model\Map\UrlRewriteMap;
use Magento\Framework\App\ObjectManager;
use Magento\UrlRewrite\Model\UrlRewritesSet;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrentUrlRewritesRegenerator
{
    /**
     * @var UrlFinderInterface
     * @deprecated
     */
    protected $urlFinder;

    /** @var ProductUrlPathGenerator */
    protected $productUrlPathGenerator;

    /** @var UrlRewriteFactory */
    protected $urlRewriteFactory;

    /** @var UrlRewrite */
    private $urlRewritePlaceholder;

    /** @var UrlRewriteMap */
    private $urlRewriteMap;

    /** @var \Magento\UrlRewrite\Model\UrlRewritesSet */
    private $urlRewritesSet;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param UrlRewriteMap|null $urlRewriteMap
     * @param \Magento\UrlRewrite\Model\UrlRewritesSet|null $urlRewritesSet
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        ProductUrlPathGenerator $productUrlPathGenerator,
        UrlRewriteFactory $urlRewriteFactory,
        UrlRewriteMap $urlRewriteMap = null,
        UrlRewritesSet $urlRewritesSet = null
    ) {
        $this->urlFinder = $urlFinder;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlRewritePlaceholder = $urlRewriteFactory->create();
        $this->urlRewriteMap = $urlRewriteMap ?: ObjectManager::getInstance()->get(UrlRewriteMap::class);
        $this->urlRewritesSet = $urlRewritesSet ?: ObjectManager::getInstance()->get(UrlRewritesSet::class);
    }

    /**
     * Generate list based on current rewrites
     *
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @param int|null $rootCategoryId
     * @return UrlRewrite[]
     */
    public function generate($storeId, Product $product, ObjectRegistry $productCategories, $rootCategoryId = null)
    {
        $currentUrlRewrites = $this->urlRewriteMap->getByIdentifiers(
            $product->getEntityId(),
            $storeId,
            ProductUrlRewriteGenerator::ENTITY_TYPE,
            $rootCategoryId
        );

        foreach ($currentUrlRewrites as $currentUrlRewrite) {
            $category = $this->retrieveCategoryFromMetadata($currentUrlRewrite, $productCategories);
            if ($category === false) {
                continue;
            }
            $this->urlRewritesSet->merge(
                $currentUrlRewrite->getIsAutogenerated()
                ? $this->generateForAutogenerated($currentUrlRewrite, $storeId, $category, $product)
                : $this->generateForCustom($currentUrlRewrite, $storeId, $category, $product)
            );
        }

        $result = $this->urlRewritesSet->getData();
        $this->urlRewritesSet->resetData();
        return $result;
    }

    /**
     * @param UrlRewrite $url
     * @param int $storeId
     * @param Category|null $category
     * @param Product $product
     * @return UrlRewrite[]
     */
    protected function generateForAutogenerated(UrlRewrite $url, $storeId, $category, Product $product)
    {
        if ($product->getData('save_rewrites_history')) {
            $targetPath = $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category);
            if ($url->getRequestPath() !== $targetPath) {
                $generatedUrl = clone $this->urlRewritePlaceholder;
                $generatedUrl->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                    ->setEntityId($product->getEntityId())
                    ->setRequestPath($url->getRequestPath())
                    ->setTargetPath($targetPath)
                    ->setRedirectType(OptionProvider::PERMANENT)
                    ->setStoreId($storeId)
                    ->setDescription($url->getDescription())
                    ->setIsAutogenerated(1)
                    ->setMetadata($url->getMetadata());
                return [$generatedUrl];
            }
        }
        return [];
    }

    /**
     * @param UrlRewrite $url
     * @param int $storeId
     * @param Category|null $category
     * @param Product $product
     * @return UrlRewrite[]
     */
    protected function generateForCustom(UrlRewrite $url, $storeId, $category, Product $product)
    {
        $targetPath = $url->getRedirectType()
            ? $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category)
            : $url->getTargetPath();
        if ($url->getRequestPath() !== $targetPath) {
            $generatedUrl = clone $this->urlRewritePlaceholder;
            $generatedUrl->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($product->getEntityId())
                ->setRequestPath($url->getRequestPath())
                ->setTargetPath($targetPath)
                ->setRedirectType($url->getRedirectType())
                ->setStoreId($storeId)
                ->setDescription($url->getDescription())
                ->setIsAutogenerated(0)
                ->setMetadata($url->getMetadata());
            return [$generatedUrl];
        }
        return [];
    }

    /**
     * @param UrlRewrite $url
     * @param ObjectRegistry $productCategories
     * @return Category|null|bool
     */
    protected function retrieveCategoryFromMetadata(UrlRewrite $url, ObjectRegistry $productCategories)
    {
        $metadata = $url->getMetadata();
        if (isset($metadata['category_id'])) {
            $category = $productCategories->get($metadata['category_id']);
            return $category === null ? false : $category;
        }
        return null;
    }
}
