<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\CatalogUrlRewrite\Model\Map\CategoryUrlRewriteMap;
use Magento\CatalogUrlRewrite\Model\Map\MapPoolInterface;
use Magento\Framework\App\ObjectManager;

class CurrentUrlRewritesRegenerator
{
    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory */
    protected $urlRewriteFactory;

    /** @var UrlFinderInterface */
    protected $urlFinder;

    /** @var MapPoolInterface */
    private $mapPool;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory
     * @param UrlFinderInterface $urlFinder
     * @param MapPoolInterface|null $mapPool
     */
    public function __construct(
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        UrlRewriteFactory $urlRewriteFactory,
        UrlFinderInterface $urlFinder,
        MapPoolInterface $mapPool = null
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlFinder = $urlFinder;
        $this->mapPool = $mapPool ?: ObjectManager::getInstance()->get(MapPoolInterface::class);
    }

    /**
     * Generate list based on current url rewrites
     *
     * @param int $storeId
     * @param \Magento\Catalog\Model\Category $category
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generate($storeId, Category $category, $rootCategoryId = null)
    {
        if ($rootCategoryId) {
            $categoryUrlRewriteMap = $this->mapPool->getMap(CategoryUrlRewriteMap::class, $rootCategoryId);

            /** @var UrlRewrite[] $currentUrlRewrites */
            $currentUrlRewrites = $categoryUrlRewriteMap->getByIdentifiers(
                [
                    UrlRewrite::STORE_ID => $storeId,
                    UrlRewrite::ENTITY_ID => $category->getEntityId()
                ]
            );
        } else {
            $currentUrlRewrites = $this->urlFinder->findAllByData(
                [
                    UrlRewrite::STORE_ID => $storeId,
                    UrlRewrite::ENTITY_ID => $category->getEntityId(),
                    UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteMap::ENTITY_TYPE,
                ]
            );
        }

        $urlRewrites = [];
        foreach ($currentUrlRewrites as $rewrite) {
            $urls = $rewrite->getIsAutogenerated()
                ? $this->generateForAutogenerated($rewrite, $storeId, $category)
                : $this->generateForCustom($rewrite, $storeId, $category);
            foreach ($urls as $url) {
                $urlRewrites[$url->getRequestPath() . '_' . $url->getStoreId()] = $url;
            }
            unset($urls);
        }
        return $urlRewrites;
    }

    /**
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $url
     * @param int $storeId
     * @param Category $category
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForAutogenerated($url, $storeId, Category $category)
    {
        $urls = [];
        if ($category->getData('save_rewrites_history')) {
            $targetPath = $this->categoryUrlPathGenerator->getUrlPathWithSuffix($category, $storeId);
            if ($url->getRequestPath() !== $targetPath) {
                $urls[$url->getRequestPath() . '_' . $storeId] = $this->urlRewriteFactory->create()
                    ->setEntityType(CategoryUrlRewriteGenerator::ENTITY_TYPE)
                    ->setEntityId($category->getEntityId())
                    ->setRequestPath($url->getRequestPath())
                    ->setTargetPath($targetPath)
                    ->setRedirectType(OptionProvider::PERMANENT)
                    ->setStoreId($storeId)
                    ->setIsAutogenerated(0);
            }
        }
        return $urls;
    }

    /**
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $url
     * @param int $storeId
     * @param Category $category
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForCustom($url, $storeId, Category $category)
    {
        $urls = [];
        $targetPath = !$url->getRedirectType()
            ? $url->getTargetPath()
            : $this->categoryUrlPathGenerator->getUrlPathWithSuffix($category, $storeId);
        if ($url->getRequestPath() !== $targetPath) {
            $urls[$url->getRequestPath() . '_' . $storeId] = $this->urlRewriteFactory->create()
                ->setEntityType(CategoryUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($category->getEntityId())
                ->setRequestPath($url->getRequestPath())
                ->setTargetPath($targetPath)
                ->setRedirectType($url->getRedirectType())
                ->setStoreId($storeId)
                ->setDescription($url->getDescription())
                ->setIsAutogenerated(0)
                ->setMetadata($url->getMetadata());
        }
        return $urls;
    }
}
