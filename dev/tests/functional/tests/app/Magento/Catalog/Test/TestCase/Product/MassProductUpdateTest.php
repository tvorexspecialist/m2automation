<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductActionAttributeEdit;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Precondition:
 * 1. Product is created.
 * 2. Product flat is enabled.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Go to Products -> Catalog.
 * 3. Find Product (from preconditions) in Products grid.
 * 4. Select Product's check-box.
 * 5. Select "Update Attributes" value in "Select Product Actions" drop-down list.
 * 6. Click on the "Submit" button.
 * 7. Open "Attributes" tab.
 * 8. Fill data.
 * 9. Click on the "Save" button.
 * 10. Perform asserts.
 *
 * @group Products_(MX)
 * @ZephyrId MAGETWO-21128
 */
class MassProductUpdateTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Product page with a grid.
     *
     * @var CatalogProductIndex
     */
    protected $productGrid;

    /**
     * Page to update a product.
     *
     * @var CatalogProductActionAttributeEdit
     */
    protected $attributeMassActionPage;

    /**
     * Configuration data.
     *
     * @var string
     */
    protected $configData;

    /**
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Injection data.
     *
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductActionAttributeEdit $attributeMassActionPage
     * @param TestStepFactory $testStepFactory
     * @param FixtureFactory $fixtureFactory
     */
    public function __inject(
        CatalogProductIndex $productGrid,
        CatalogProductActionAttributeEdit $attributeMassActionPage,
        TestStepFactory $testStepFactory,
        FixtureFactory $fixtureFactory
    ) {
        $this->productGrid = $productGrid;
        $this->attributeMassActionPage = $attributeMassActionPage;
        $this->testStepFactory = $testStepFactory;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Run mass update product simple entity test.
     *
     * @param CatalogProductSimple $product
     * @param string $configData
     * @param array $initialProducts
     * @param string $checkbox
     * @return array
     */
    public function test(
        CatalogProductSimple $product,
        $configData,
        array $initialProducts,
        $checkbox
    ) {
        $this->configData = $configData;

        // Preconditions
        $products = $this->testStepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $initialProducts]
        )->run()['products'];

        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();

        // Steps
        $this->productGrid->open();
        $this->productGrid->getProductGrid()->updateAttributes($products);
        $this->attributeMassActionPage->getAttributesBlockForm()->fillForm($product, $checkbox);
        $this->attributeMassActionPage->getFormPageActions()->save();

        $productsReturn = [];
        /** @var FixtureInterface $item */
        foreach ($products as $item) {
            $productsReturn[] = $this->fixtureFactory->create(
                get_class($item),
                ['data' => array_merge($item->getData(), $product->getData())]
            );
        }
        
        return ['products' => $productsReturn];
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
