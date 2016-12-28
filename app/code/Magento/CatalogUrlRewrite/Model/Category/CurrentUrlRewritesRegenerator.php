<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Model\OptionProvider;

class CurrentUrlRewritesRegenerator
{
    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory */
    protected $urlRewriteFactory;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite */
    private $urlRewritePlaceholder;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     * @deprecated
     */
    protected $urlFinder;

    /** @var \Magento\CatalogUrlRewrite\Model\Map\UrlRewriteMap */
    private $urlRewriteMap;

    /** @var \Magento\UrlRewrite\Model\UrlRewritesSet */
    private $urlRewritesSetPlaceHolder;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     * @param \Magento\CatalogUrlRewrite\Model\Map\UrlRewriteMap|null $urlRewriteMap
     * @param \Magento\UrlRewrite\Model\UrlRewritesSetFactory|null $urlRewritesSetFactory
     */
    public function __construct(
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Magento\CatalogUrlRewrite\Model\Map\UrlRewriteMap $urlRewriteMap = null,
        \Magento\UrlRewrite\Model\UrlRewritesSetFactory $urlRewritesSetFactory = null
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlRewritePlaceholder = $urlRewriteFactory->create();
        $this->urlFinder = $urlFinder;
        $this->urlRewriteMap = $urlRewriteMap ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\CatalogUrlRewrite\Model\Map\UrlRewriteMap::class);
        $urlRewritesSetFactory = $urlRewritesSetFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(UrlRewritesSetFactory::class);
        $this->urlRewritesSetPlaceHolder = $urlRewritesSetFactory->create();
    }

    /**
     * Generate list based on current url rewrites
     *
     * @param int $storeId
     * @param \Magento\Catalog\Model\Category $category
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generate($storeId, \Magento\Catalog\Model\Category $category, $rootCategoryId = null)
    {
        $urlRewritesSet = clone $this->urlRewritesSetPlaceHolder;
        $currentUrlRewrites = $this->urlRewriteMap->getByIdentifiers(
            $category->getEntityId(),
            $storeId,
            CategoryUrlRewriteGenerator::ENTITY_TYPE,
            $rootCategoryId
        );

        foreach ($currentUrlRewrites as $rewrite) {
            $urlRewritesSet->merge(
                $rewrite->getIsAutogenerated()
                ? $this->generateForAutogenerated($rewrite, $storeId, $category)
                : $this->generateForCustom($rewrite, $storeId, $category)
            );
        }

        $result = $urlRewritesSet->getData();
        $urlRewritesSet->resetData();
        return $result;
    }

    /**
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $url
     * @param int $storeId
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForAutogenerated($url, $storeId, \Magento\Catalog\Model\Category $category)
    {
        if ($category->getData('save_rewrites_history')) {
            $targetPath = $this->categoryUrlPathGenerator->getUrlPathWithSuffix($category, $storeId);
            if ($url->getRequestPath() !== $targetPath) {
                $generatedUrl = clone $this->urlRewritePlaceholder;
                $generatedUrl->setEntityType(CategoryUrlRewriteGenerator::ENTITY_TYPE)
                    ->setEntityId($category->getEntityId())
                    ->setRequestPath($url->getRequestPath())
                    ->setTargetPath($targetPath)
                    ->setRedirectType(OptionProvider::PERMANENT)
                    ->setStoreId($storeId)
                    ->setIsAutogenerated(0);
                return [$generatedUrl];
            }
        }
        return [];
    }

    /**
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $url
     * @param int $storeId
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForCustom($url, $storeId, \Magento\Catalog\Model\Category $category)
    {
        $targetPath = !$url->getRedirectType()
            ? $url->getTargetPath()
            : $this->categoryUrlPathGenerator->getUrlPathWithSuffix($category, $storeId);
        if ($url->getRequestPath() !== $targetPath) {
            $generatedUrl = clone $this->urlRewritePlaceholder;
            $generatedUrl->setEntityType(CategoryUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($category->getEntityId())
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
}
