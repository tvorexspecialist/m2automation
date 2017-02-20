<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Constraint;

use Magento\Captcha\Test\Page\CustomerAccountLoginWithCaptcha;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert captcha on storefront login page.
 */
class AssertCaptchaFieldOnStorefront extends AbstractConstraint
{
    /**
     * Assert captcha and reload button visibility on storefront login page.
     *
     * @param CustomerAccountLoginWithCaptcha $loginPage
     * @return void
     */
    public function processAssert(CustomerAccountLoginWithCaptcha $loginPage)
    {
        \PHPUnit_Framework_Assert::assertTrue(
            $loginPage->getLoginBlockWithCaptcha()->getCaptcha()->isVisible(),
            'Captcha image is not present on storefront login page'
        );

        \PHPUnit_Framework_Assert::assertTrue(
            $loginPage->getLoginBlockWithCaptcha()->getCaptchaReloadButton()->isVisible(),
            'Captcha reload button is not present on storefront login page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Captcha and reload button are presents on storefront login page.';
    }
}
