<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategorySaveMessage
 * Assert that success message is displayed after category save
 */
class AssertCategorySaveMessage extends AbstractConstraint
{
    /**
     * Success category save message
     */
    const SUCCESS_MESSAGE = 'You saved the category.';

    /**
     * Assert that success message is displayed after category save
     *
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @return void
     */
    public function processAssert(CatalogCategoryEdit $catalogCategoryEdit)
    {
        $actualMessage = $catalogCategoryEdit->getMessagesBlock()->getSuccessMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Success message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message is displayed.';
    }
}
