<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage\Payment;

use Magento\Mtf\Block\Form;
use Magento\Payment\Test\Fixture\CreditCard;

/**
 * One page checkout status payment method block
 *
 */
class Methods extends Form
{
    /**
     * Payment method input selector
     *
     * @var string
     */
    protected $paymentMethodInput = '#%s';

    /**
     * Labels for payment methods
     *
     * @var string
     */
    protected $paymentMethodLabels = '.payment-method:not([style="display: none;"]) .payment-method-title label';

    /**
     * Label for payment methods
     *
     * @var string
     */
    protected $paymentMethodLabel = '[for="%s"]';

    /**
     * Continue checkout button
     *
     * @var string
     */
    protected $continue = '#payment-buttons-container button';

    /**
     * Place order button
     *
     * @var string
     */
    protected $placeOrder = '.action.primary.checkout';

    /**
     * Wait element
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * Purchase order number selector
     *
     * @var string
     */
    protected $purchaseOrderNumber = '#po_number';

    /**
     * Select payment method
     *
     * @param array $payment
     * @param CreditCard|null $creditCard
     * @throws \Exception
     * @return void
     */
    public function selectPaymentMethod(array $payment, CreditCard $creditCard = null)
    {
        $paymentSelector = $this->_rootElement->find(sprintf($this->paymentMethodInput, $payment['method']));
        if ($paymentSelector->isVisible()) {
            $paymentSelector->click();
        } else {
            $paymentCount = count($this->_rootElement->getElements($this->paymentMethodLabels));
            $paymentLabel = $this->_rootElement->find(sprintf($this->paymentMethodLabel, $payment['method']));
            if ($paymentCount !== 1 && !$paymentLabel->isVisible()) {
                throw new \Exception('Such payment method is absent.');
            }
        }
        if ($payment['method'] == "purchaseorder") {
            $this->_rootElement->find($this->purchaseOrderNumber)->setValue($payment['po_number']);
        }
        if ($creditCard !== null) {
            /** @var \Magento\Payment\Test\Block\Form\Cc $formBlock */
            $formBlock = $this->blockFactory->create(
                '\\Magento\\Payment\\Test\\Block\\Form\\Cc',
                ['element' => $this->_rootElement->find('#payment_form_' . $payment['method'])]
            );
            $formBlock->fill($creditCard);
        }
    }

    /**
     * Press "Continue" button
     *
     * @return void
     */
    public function clickContinue()
    {
        $this->_rootElement->find($this->continue)->click();
        $browser = $this->browser;
        $selector = $this->waitElement;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() == false ? true : null;
            }
        );
    }

    /*
     * Press "Place Order" button
     */
    public function placeOrder()
    {
        $this->_rootElement->find($this->placeOrder)->click();
        $browser = $this->browser;
        $selector = $this->waitElement;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() == false ? true : null;
            }
        );
    }
}
