<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Store\Test\Fixture\Store;

/**
 * Class AssertCategoryUrlWithCustomStoreView.
 */
class AssertCategoryUrlWithCustomStoreView extends AbstractConstraint
{
    /**
     * Assert that displayed category data on category page equals to passed from fixture.
     *
     * @param Store $storeView
     * @param Category $childCategory
     * @param Category $parentCategory
     * @param Category $categoryUpdates
     * @param CmsIndex $cmsIndex
     * @param BrowserInterface $browser
     */
    public function processAssert(
        Store $storeView,
        Category $childCategory,
        Category $parentCategory,
        Category $categoryUpdates,
        CmsIndex $cmsIndex,
        BrowserInterface $browser
    ) {
        $cmsIndex->open();
        $cmsIndex->getStoreSwitcherBlock()->selectStoreView($storeView->getName());
        $cmsIndex->getTopmenu()->hoverCategoryByName($parentCategory->getName());
        $cmsIndex->getTopmenu()->selectCategoryByName(
            $childCategory->getName()
        );
        $actualUrl = strtolower($parentCategory->getUrlKey() . '/' . $categoryUpdates->getUrlKey());
        $result = (bool)strpos($browser->getUrl(), $actualUrl);

        \PHPUnit_Framework_Assert::assertTrue(
            $result,
            "Category URL is not correct."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category URL is correct.';
    }
}
