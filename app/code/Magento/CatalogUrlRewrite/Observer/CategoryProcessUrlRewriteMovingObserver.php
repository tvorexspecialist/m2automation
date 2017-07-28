<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Block\UrlKeyRenderer;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;

/**
 * Generates Category Url Rewrites after move/save and Products Url Rewrites assigned to the category that's being saved
 * @since 2.0.0
 */
class CategoryProcessUrlRewriteMovingObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator
     * @since 2.0.0
     */
    protected $categoryUrlRewriteGenerator;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     * @since 2.0.0
     */
    protected $urlPersist;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler
     * @since 2.0.0
     */
    protected $urlRewriteHandler;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer
     * @since 2.2.0
     */
    private $urlRewriteBunchReplacer;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool
     * @since 2.2.0
     */
    private $databaseMapPool;

    /**
     * @var string[]
     * @since 2.2.0
     */
    private $dataUrlRewriteClassNames;

    /**
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlRewriteHandler $urlRewriteHandler
     * @param UrlRewriteBunchReplacer $urlRewriteBunchReplacer
     * @param \Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool $databaseMapPool
     * @param string[] $dataUrlRewriteClassNames
     * @since 2.0.0
     */
    public function __construct(
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ScopeConfigInterface $scopeConfig,
        UrlRewriteHandler $urlRewriteHandler,
        UrlRewriteBunchReplacer $urlRewriteBunchReplacer,
        DatabaseMapPool $databaseMapPool,
        $dataUrlRewriteClassNames = [
        DataCategoryUrlRewriteDatabaseMap::class,
        DataProductUrlRewriteDatabaseMap::class
        ]
    ) {
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->scopeConfig = $scopeConfig;
        $this->urlRewriteHandler = $urlRewriteHandler;
        $this->urlRewriteBunchReplacer = $urlRewriteBunchReplacer;
        $this->databaseMapPool = $databaseMapPool;
        $this->dataUrlRewriteClassNames = $dataUrlRewriteClassNames;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        if ($category->dataHasChangedFor('parent_id')) {
            $saveRewritesHistory = $this->scopeConfig->isSetFlag(
                UrlKeyRenderer::XML_PATH_SEO_SAVE_HISTORY,
                ScopeInterface::SCOPE_STORE,
                $category->getStoreId()
            );
            $category->setData('save_rewrites_history', $saveRewritesHistory);
            $categoryUrlRewriteResult = $this->categoryUrlRewriteGenerator->generate($category, true);
            $this->urlRewriteHandler->deleteCategoryRewritesForChildren($category);
            $this->urlRewriteBunchReplacer->doBunchReplace($categoryUrlRewriteResult);
            $productUrlRewriteResult = $this->urlRewriteHandler->generateProductUrlRewrites($category);
            $this->urlRewriteBunchReplacer->doBunchReplace($productUrlRewriteResult);
            //frees memory for maps that are self-initialized in multiple classes that were called by the generators
            $this->resetUrlRewritesDataMaps($category);
        }
    }

    /**
     * Resets used data maps to free up memory and temporary tables
     *
     * @param Category $category
     * @return void
     * @since 2.2.0
     */
    private function resetUrlRewritesDataMaps($category)
    {
        foreach ($this->dataUrlRewriteClassNames as $className) {
            $this->databaseMapPool->resetMap($className, $category->getEntityId());
        }
    }
}
