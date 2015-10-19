<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

class TaxConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Tax\Model\TaxConfigProvider
     */
    protected $model;

    protected function setUp()
    {
        $this->taxHelperMock = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);
        $this->taxConfigMock = $this->getMock('Magento\Tax\Model\Config', [], [], '', false);
        $this->checkoutSessionMock = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $this->scopeConfigMock = $this->getMock(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );
        $this->quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);
        $this->model = new \Magento\Tax\Model\TaxConfigProvider(
            $this->taxHelperMock,
            $this->taxConfigMock,
            $this->checkoutSessionMock,
            $this->scopeConfigMock
        );
    }

    /**
     * @dataProvider getConfigDataProvider
     * @param array $expectedResult
     * @param int $cartShippingBoth
     * @param int $cartShippingExclTax
     * @param int $cartBothPrices
     * @param int $cartPriceExclTax
     * @param int $cartSubTotalBoth
     * @param int $cartSubTotalExclTax
     * @param string|null $calculationType
     * @param bool $isQuoteVirtual
     */
    public function testGetConfig(
        $expectedResult,
        $cartShippingBoth,
        $cartShippingExclTax,
        $cartBothPrices,
        $cartPriceExclTax,
        $cartSubTotalBoth,
        $cartSubTotalExclTax,
        $calculationType,
        $isQuoteVirtual
    ) {
        $this->taxConfigMock->expects($this->any())->method('displayCartShippingBoth')
            ->will($this->returnValue($cartShippingBoth));
        $this->taxConfigMock->expects($this->any())->method('displayCartShippingExclTax')
            ->will($this->returnValue($cartShippingExclTax));

        $this->taxHelperMock->expects($this->any())->method('displayCartBothPrices')
            ->will($this->returnValue($cartBothPrices));
        $this->taxHelperMock->expects($this->any())->method('displayCartPriceExclTax')
            ->will($this->returnValue($cartPriceExclTax));

        $this->taxConfigMock->expects($this->any())->method('displayCartSubtotalBoth')
            ->will($this->returnValue($cartSubTotalBoth));
        $this->taxConfigMock->expects($this->any())->method('displayCartSubtotalExclTax')
            ->will($this->returnValue($cartSubTotalExclTax));

        $this->taxHelperMock->expects(($this->any()))->method('displayShippingPriceExcludingTax')
            ->will($this->returnValue(1));
        $this->taxHelperMock->expects(($this->any()))->method('displayShippingBothPrices')
            ->will($this->returnValue(1));
        $this->taxHelperMock->expects(($this->any()))->method('displayFullSummary')
            ->will($this->returnValue(1));
        $this->taxConfigMock->expects(($this->any()))->method('displayCartTaxWithGrandTotal')
            ->will($this->returnValue(1));
        $this->taxConfigMock->expects(($this->any()))->method('displayCartZeroTax')
            ->will($this->returnValue(1));
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(\Magento\Tax\Model\Config::CONFIG_XML_PATH_BASED_ON)
            ->willReturn($calculationType);
        $this->quoteMock->expects($this->any())->method('isVirtual')->willReturn($isQuoteVirtual);
        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'both',
                    'reviewItemPriceDisplayMode' => 'both',
                    'reviewTotalsDisplayMode' => 'both',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => false,
                ],
                'cartShippingBoth' => 1,
                'cartShippingExclTax' => 1,
                'cartBothPrices' => 1,
                'cartPriceExclTax' => 1,
                'cartSubTotalBoth' => 1,
                'cartSubTotalExclTax' => 1,
                'calculationType' => 'shipping',
                'isQuoteVirtual' => false,
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'excluding',
                    'reviewItemPriceDisplayMode' => 'excluding',
                    'reviewTotalsDisplayMode' => 'excluding',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => true,
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 1,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 1,
                'cartSubTotalBoth' => 0,
                'cartSubTotalExclTax' => 1,
                'calculationType' => 'billing',
                'isQuoteVirtual' => false,
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'including',
                    'reviewItemPriceDisplayMode' => 'including',
                    'reviewTotalsDisplayMode' => 'including',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => true,
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 0,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 0,
                'cartSubTotalExclTax' => 0,
                'calculationType' => 'shipping',
                'isQuoteVirtual' => true,
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'including',
                    'reviewItemPriceDisplayMode' => 'including',
                    'reviewTotalsDisplayMode' => 'including',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => true,
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 0,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 0,
                'cartSubTotalExclTax' => 0,
                'calculationType' => 'billing',
                'isQuoteVirtual' => true,
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'both',
                    'reviewItemPriceDisplayMode' => 'both',
                    'reviewTotalsDisplayMode' => 'both',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => false,
                ],
                'cartShippingBoth' => 1,
                'cartShippingExclTax' => 0,
                'cartBothPrices' => 1,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 1,
                'cartSubTotalExclTax' => 0,
                'calculationType' => 'shipping',
                'isQuoteVirtual' => false,
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'excluding',
                    'reviewItemPriceDisplayMode' => 'including',
                    'reviewTotalsDisplayMode' => 'both',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => false,
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 1,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 1,
                'cartSubTotalExclTax' => 0,
                'calculationType' => 'shipping',
                'isQuoteVirtual' => false,
            ],
        ];
    }
}
