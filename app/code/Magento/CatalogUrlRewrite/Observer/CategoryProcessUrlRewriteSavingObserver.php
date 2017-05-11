<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\Framework\Event\ObserverInterface;

class CategoryProcessUrlRewriteSavingObserver implements ObserverInterface
{
    /** @var CategoryUrlRewriteGenerator */
    private $categoryUrlRewriteGenerator;

    /** @var UrlRewriteBunchReplacer */
    private $urlRewriteBunchReplacer;

    /** @var UrlRewriteHandler */
    private $urlRewriteHandler;

    /** @var DatabaseMapPool */
    private $databaseMapPool;

    /** @var string[] */
    private $dataUrlRewriteClassNames;

    /**
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param UrlRewriteHandler $urlRewriteHandler
     * @param UrlRewriteBunchReplacer $urlRewriteBunchReplacer
     * @param DatabaseMapPool $databaseMapPool
     * @param string[] $dataUrlRewriteClassNames
     */
    public function __construct(
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlRewriteHandler $urlRewriteHandler,
        UrlRewriteBunchReplacer $urlRewriteBunchReplacer,
        DatabaseMapPool $databaseMapPool,
        $dataUrlRewriteClassNames = [
        DataCategoryUrlRewriteDatabaseMap::class,
        DataProductUrlRewriteDatabaseMap::class
        ]
    ) {
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlRewriteHandler = $urlRewriteHandler;
        $this->urlRewriteBunchReplacer = $urlRewriteBunchReplacer;
        $this->databaseMapPool = $databaseMapPool;
        $this->dataUrlRewriteClassNames = $dataUrlRewriteClassNames;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getData('category');
        if ($category->getParentId() == Category::TREE_ROOT_ID) {
            return;
        }
        if ($category->dataHasChangedFor('url_key')
            || $category->dataHasChangedFor('is_anchor')
            || $category->getIsChangedProductList()
        ) {
            $categoryUrlRewriteResult = $this->categoryUrlRewriteGenerator->generate($category);
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
     */
    private function resetUrlRewritesDataMaps($category)
    {
        foreach ($this->dataUrlRewriteClassNames as $className) {
            $this->databaseMapPool->resetMap($className, $category->getEntityId());
        }
    }
}
