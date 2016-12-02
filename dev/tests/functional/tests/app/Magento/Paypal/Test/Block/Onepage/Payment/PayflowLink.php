<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Onepage\Payment;

use Magento\Checkout\Test\Block\Onepage\Payment\Method;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\ObjectManager;
use Magento\Paypal\Test\Block\Form\PayflowLink\Cc;

/**
 * Payflow Link credit card block.
 */
class PayflowLink extends Method
{
    /**
     * 'Pay Now' button selector.
     *
     * @var string
     */
    private $payNowButton = '#btn_pay_cc';

    /**
     * PayPal iframe selector.
     *
     * @var string
     */
    private $paypalIframe = '.paypal.iframe';

    /**
     * Credit card form selector.
     *
     * @var string
     */
    private $creditCardForm = '#fieldsCC';

    /**
     * Fill credit card data in PayPal iframe form.
     *
     * @param FixtureInterface $creditCard
     * @return void
     */
    public function fillPaymentData(FixtureInterface $creditCard)
    {
        $iframeRootElement = $this->switchToPaypalFrame();
        /** @var Cc $formBlock */
        $formBlock = $this->blockFactory->create(
            Cc::class,
            ['element' => $this->_rootElement->find($this->creditCardForm)]
        );
        $formBlock->fill($creditCard, $iframeRootElement);
        $iframeRootElement->find($this->payNowButton)->click();
        $this->browser->switchToFrame();
    }

    /**
     * Change the focus to a PayPal frame.
     *
     * @return ElementInterface
     */
    private function switchToPaypalFrame()
    {
        $iframeLocator = ObjectManager::getInstance()->create(
            Locator::class,
            ['value' => $this->paypalIframe]
        );
        $this->browser->switchToFrame($iframeLocator);
        return $this->browser->find('body');
    }
}
