<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep;

/**
 * Selecting checkout method.
 */
class SelectCheckoutMethodStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Checkout method.
     *
     * @var string
     */
    protected $checkoutMethod;

    /**
     * Customer fixture.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Logout customer on frontend step.
     *
     * @var LogoutCustomerOnFrontendStep
     */
    protected $logoutCustomerOnFrontend;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param Customer $customer
     * @param LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend
     * @param string $checkoutMethod
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        Customer $customer,
        LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend,
        $checkoutMethod
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->customer = $customer;
        $this->logoutCustomerOnFrontend = $logoutCustomerOnFrontend;
        $this->checkoutMethod = $checkoutMethod;
    }

    /**
     * Run step that selecting checkout method.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $checkoutMethodBlock = $this->checkoutOnepage->getLoginBlock();
        switch ($this->checkoutMethod) {
            case 'guest':
                $checkoutMethodBlock->clickContinue();
                break;
            case 'login':
                $checkoutMethodBlock->loginCustomer($this->customer);
                break;
            default:
                throw new \Exception("Undefined checkout method.");
                break;
        }
    }

    /**
     * Logout customer on fronted.
     *
     * @return void
     */
    public function cleanup()
    {
        if ($this->checkoutMethod === 'login') {
            $this->logoutCustomerOnFrontend->run();
        }
    }
}
