<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Class \Magento\Catalog\Model\CategoryManagement
 *
 * @since 2.0.0
 */
class CategoryManagement implements \Magento\Catalog\Api\CategoryManagementInterface
{
    /**
     * @var CategoryRepository
     * @since 2.0.0
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Category\Tree
     * @since 2.0.0
     */
    protected $categoryTree;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     * @since 2.1.0
     */
    private $scopeResolver;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     * @since 2.1.0
     */
    private $categoriesFactory;
    
    /**
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param Category\Tree $categoryTree
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoriesFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Category\Tree $categoryTree,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoriesFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryTree = $categoryTree;
        $this->categoriesFactory = $categoriesFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTree($rootCategoryId = null, $depth = null)
    {
        $category = null;
        if ($rootCategoryId !== null) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->categoryRepository->get($rootCategoryId);
        } elseif ($this->isAdminStore()) {
            $category = $this->getTopLevelCategory();
        }
        $result = $this->categoryTree->getTree($this->categoryTree->getRootNode($category), $depth);
        return $result;
    }

    /**
     * Check is request use default scope
     *
     * @return bool
     * @since 2.1.0
     */
    private function isAdminStore()
    {
        return $this->getScopeResolver()->getScope()->getCode() == \Magento\Store\Model\Store::ADMIN_CODE;
    }

    /**
     * Get store manager for operations with admin code
     *
     * @return \Magento\Framework\App\ScopeResolverInterface
     * @since 2.1.0
     */
    private function getScopeResolver()
    {
        if ($this->scopeResolver == null) {
            $this->scopeResolver = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\App\ScopeResolverInterface::class);
        }

        return $this->scopeResolver;
    }

    /**
     * Get top level hidden root category
     *
     * @return \Magento\Catalog\Model\Category
     * @since 2.1.0
     */
    private function getTopLevelCategory()
    {
        $categoriesCollection = $this->categoriesFactory->create();
        return $categoriesCollection->addFilter('level', ['eq' => 0])->getFirstItem();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function move($categoryId, $parentId, $afterId = null)
    {
        $model = $this->categoryRepository->get($categoryId);
        $parentCategory = $this->categoryRepository->get($parentId);

        if ($parentCategory->hasChildren()) {
            $parentChildren = $parentCategory->getChildren();
            $categoryIds = explode(',', $parentChildren);
            $lastId = array_pop($categoryIds);
            $afterId = ($afterId === null || $afterId > $lastId) ? $lastId : $afterId;
        }

        if (strpos($parentCategory->getPath(), $model->getPath()) === 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Operation do not allow to move a parent category to any of children category')
            );
        }
        try {
            $model->move($parentId, $afterId);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Could not move category'));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCount()
    {
        $categories = $this->categoriesFactory->create();
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
        $categories->addAttributeToFilter('parent_id', ['gt' => 0]);
        return $categories->getSize();
    }
}
