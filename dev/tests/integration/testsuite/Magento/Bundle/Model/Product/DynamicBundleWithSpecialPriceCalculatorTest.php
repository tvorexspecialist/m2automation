<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product.php
 * @magentoAppArea frontend
 */
class DynamicBundleWithSpecialPriceCalculatorTest extends BundlePriceAbstract
{
    /**
     * @param array $strategyModifiers
     * @param array $expectedResults
     * @dataProvider getTestCases
     * @magentoAppIsolation enabled
     */
    public function testPriceForDynamicBundle(array $strategyModifiers, array $expectedResults)
    {
        $this->prepareFixture($strategyModifiers, 'bundle_product');
        $bundleProduct = $this->productRepository->get('bundle_product', false, null, true);

        /** @var \Magento\Framework\Pricing\PriceInfo\Base $priceInfo */
        $priceInfo = $bundleProduct->getPriceInfo();
        $priceCode = \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE;

        $this->assertEquals(
            $expectedResults['minimalPrice'],
            $priceInfo->getPrice($priceCode)->getMinimalPrice()->getValue(),
            'Failed to check minimal price on product'
        );

        $this->assertEquals(
            $expectedResults['maximalPrice'],
            $priceInfo->getPrice($priceCode)->getMaximalPrice()->getValue(),
            'Failed to check maximal price on product'
        );
    }

    /**
     * Test cases for current test
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTestCases()
    {
        return [
            '
                #1 Testing price for dynamic bundle 
                with one required option and special price
            ' => [
                'strategy' => $this->getBundleConfiguration1(),
                'expectedResults' => [
                    // 0.5 * 10
                    'minimalPrice' => 5,
                    // 0.5 * 10
                    'maximalPrice' => 5
                ]
            ],

            '
                #2 Testing price for dynamic bundle 
                with one non required option and special price
            ' => [
                'strategy' => $this->getBundleConfiguration2(),
                'expectedResults' => [
                    // 0.5 * 2 * 10
                    'minimalPrice' => 10,
                    // 0.5 * 2 * 10
                    'maximalPrice' => 10
                ]
            ],

            '
                #3 Testing price for dynamic bundle 
                with one required checkbox type option, two simples and special price
            ' => [
                'strategy' => $this->getBundleConfiguration3(),
                'expectedResults' => [
                    // 0.5 * 1 * 10
                    'minimalPrice' => 5,
                    // 0.5 * (1 * 10 + 3 * 20)
                    'maximalPrice' => 35
                ]
            ],

            '
                #4 Testing price for dynamic bundle 
                with one required multi type option, two simples and special price
            ' => [
                'strategy' => $this->getBundleConfiguration4(),
                'expectedResults' => [
                    // 0.5 * 1 * 10
                    'minimalPrice' => 5,
                    // 0.5 * (1 * 10 + 3 * 20)
                    'maximalPrice' => 35
                ]
            ],

            '
                #5 Testing price for dynamic bundle 
                with one required radio type option, two simples and special price
            ' => [
                'strategy' => $this->getBundleConfiguration5(),
                'expectedResults' => [
                    // 0.5 * 1 * 10
                    'minimalPrice' => 5,
                    // 0.5 * 3 * 20
                    'maximalPrice' => 30
                ]
            ],

            '
                #6 Testing price for dynamic bundle 
                with two required options and special price
            ' => [
                'strategy' => $this->getBundleConfiguration6(),
                'expectedResults' => [
                    // 0.5 * (1 * 10 + 1 * 10)
                    'minimalPrice' => 10,
                    // 0.5 * (3 * 20 + 1 * 10 + 3 * 20)
                    'maximalPrice' => 65
                ]
            ],

            '
                #7 Testing price for dynamic bundle 
                with one required option, one non required and special price
            ' => [
                'strategy' => $this->getBundleConfiguration7(),
                'expectedResults' => [
                    // 0.5 * (1 * 10)
                    'minimalPrice' => 5,
                    // 0.5 * (3 * 20 + 1 * 10 + 3 * 20)
                    'maximalPrice' => 65
                ]
            ],

            '
                #8 Testing price for dynamic bundle 
                with two non required options and special price
            ' => [
                'strategy' => $this->getBundleConfiguration8(),
                'expectedResults' => [
                    // 0.5 * (1 * 10)
                    'minimalPrice' => 5,
                    // 0.5 * (3 * 20 + 1 * 10 + 3 * 20)
                    'maximalPrice' => 65
                ]
            ],

            '
                #9 Testing price for dynamic bundle 
                with one simple product with special price
            ' => [
                'strategy' => $this->getBundleConfiguration9(),
                'expectedResults' => [
                    // 1 * 3.5
                    'minimalPrice' => 3.5,
                    // 1 * 20
                    'maximalPrice' => 20
                ]
            ],

            '
                #10 Testing price for dynamic bundle
                with special price and with one simple product with special price
            ' => [
                'strategy' => $this->getBundleConfiguration10(),
                'expectedResults' => [
                    // 0.5 * 1 * 3.5
                    'minimalPrice' => 1.75,
                    // 0.5 * 3 * 20
                    'maximalPrice' => 30
                ]
            ],
        ];
    }

    /**
     * Dynamic bundle with one required option and special price
     * @return array
     */
    private function getBundleConfiguration1()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSpecialPrice',
                'data' => [50]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one non required option and special price
     * @return array
     */
    private function getBundleConfiguration2()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'type' => 'checkbox',
                'required' => false,
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 2,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSpecialPrice',
                'data' => [50]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one required checkbox type option, two simples and special price
     * @return array
     */
    private function getBundleConfiguration3()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSpecialPrice',
                'data' => [50]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one required multi type option, two simples and special price
     * @return array
     */
    private function getBundleConfiguration4()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'multi',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSpecialPrice',
                'data' => [50]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one required radio type option, two simples and special price
     * @return array
     */
    private function getBundleConfiguration5()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSpecialPrice',
                'data' => [50]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with two required options and special price
     * @return array
     */
    private function getBundleConfiguration6()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ],
            [
                'title' => 'Op2',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSpecialPrice',
                'data' => [50]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one required option, one non required and special price
     * @return array
     */
    private function getBundleConfiguration7()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => false,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ],
            [
                'title' => 'Op2',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSpecialPrice',
                'data' => [50]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with two non required options and special price
     * @return array
     */
    private function getBundleConfiguration8()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => false,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ],
            [
                'title' => 'Op2',
                'required' => false,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSpecialPrice',
                'data' => [50]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with one simple product with special price
     * @return array
     */
    private function getBundleConfiguration9()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 1,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSpecialPriceForSimple',
                'data' => ['simple1', 3.5]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * Dynamic bundle with special price and with one simple product with special price
     * @return array
     */
    private function getBundleConfiguration10()
    {
        $optionsData = [
            [
                'title' => 'Op1',
                'required' => true,
                'type' => 'radio',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 1,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 3,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSpecialPriceForSimple',
                'data' => ['simple1', 3.5]
            ],
            [
                'modifierName' => 'addSpecialPrice',
                'data' => [50]
            ],
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }

    /**
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param int $discount
     * @return \Magento\Catalog\Model\Product
     */
    protected function addSpecialPrice(\Magento\Catalog\Model\Product $bundleProduct, $discount)
    {
        $bundleProduct->setSpecialPrice($discount);

        return $bundleProduct;
    }

    /**
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param string $sku
     * @param int $price
     * @return \Magento\Catalog\Model\Product
     */
    protected function addSpecialPriceForSimple(\Magento\Catalog\Model\Product $bundleProduct, $sku, $price)
    {
        $simple = $this->productRepository->get($sku, false, null, true);
        $simple->setSpecialPrice($price);
        $this->productRepository->save($simple);

        return $bundleProduct;
    }
}
