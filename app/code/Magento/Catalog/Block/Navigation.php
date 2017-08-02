<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Block;

use Magento\Catalog\Model\Category;
use Magento\Customer\Model\Context;

/**
 * Catalog navigation
 *
 * @api
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Navigation extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var Category
     * @since 2.0.0
     */
    protected $_categoryInstance;

    /**
     * Current category key
     *
     * @var string
     * @since 2.0.0
     */
    protected $_currentCategoryKey;

    /**
     * Array of level position counters
     *
     * @var array
     * @since 2.0.0
     */
    protected $_itemLevelPositions = [];

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     * @since 2.0.0
     */
    protected $_catalogCategory;

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_registry;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     * @since 2.0.0
     */
    protected $httpContext;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     * @since 2.0.0
     */
    protected $_catalogLayer;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     * @since 2.0.0
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     * @since 2.0.0
     */
    protected $flatState;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogLayer = $layerResolver->get();
        $this->httpContext = $httpContext;
        $this->_catalogCategory = $catalogCategory;
        $this->_registry = $registry;
        $this->flatState = $flatState;
        $this->_categoryInstance = $categoryFactory->create();
        parent::__construct($context, $data);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->addData(
            [
                'cache_lifetime' => false,
                'cache_tags' => [Category::CACHE_TAG, \Magento\Store\Model\Group::CACHE_TAG],
            ]
        );
    }

    /**
     * Get current category
     *
     * @return Category
     * @since 2.0.0
     */
    public function getCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     * @since 2.0.0
     */
    public function getCacheKeyInfo()
    {
        $shortCacheId = [
            'CATALOG_NAVIGATION',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getCurrentCategoryKey(),
        ];
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['category_path'] = $this->getCurrentCategoryKey();
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    /**
     * Get current category key
     *
     * @return string
     * @since 2.0.0
     */
    public function getCurrentCategoryKey()
    {
        if (!$this->_currentCategoryKey) {
            $category = $this->_registry->registry('current_category');
            if ($category) {
                $this->_currentCategoryKey = $category->getPath();
            } else {
                $this->_currentCategoryKey = $this->_storeManager->getStore()->getRootCategoryId();
            }
        }

        return $this->_currentCategoryKey;
    }

    /**
     * Retrieve child categories of current category
     *
     * @return \Magento\Framework\Data\Tree\Node\Collection
     * @since 2.0.0
     */
    public function getCurrentChildCategories()
    {
        $categories = $this->_catalogLayer->getCurrentCategory()->getChildrenCategories();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->_productCollectionFactory->create();
        $this->_catalogLayer->prepareProductCollection($productCollection);
        $productCollection->addCountToCategories($categories);
        return $categories;
    }

    /**
     * Checkin activity of category
     *
     * @param   \Magento\Framework\DataObject $category
     * @return  bool
     * @since 2.0.0
     */
    public function isCategoryActive($category)
    {
        if ($this->getCurrentCategory()) {
            return in_array($category->getId(), $this->getCurrentCategory()->getPathIds());
        }
        return false;
    }

    /**
     * Get url for category data
     *
     * @param Category $category
     * @return string
     * @since 2.0.0
     */
    public function getCategoryUrl($category)
    {
        if ($category instanceof Category) {
            $url = $category->getUrl();
        } else {
            $url = $this->_categoryInstance->setData($category->getData())->getUrl();
        }

        return $url;
    }

    /**
     * Enter description here...
     *
     * @return Category
     * @since 2.0.0
     */
    public function getCurrentCategory()
    {
        return $this->_catalogLayer->getCurrentCategory();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     * @since 2.0.0
     */
    public function getIdentities()
    {
        return [\Magento\Catalog\Model\Category::CACHE_TAG, \Magento\Store\Model\Group::CACHE_TAG];
    }
}
