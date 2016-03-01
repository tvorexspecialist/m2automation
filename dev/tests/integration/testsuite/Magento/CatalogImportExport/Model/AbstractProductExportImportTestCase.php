<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class AbstractProductExportImportTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Export\Product
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * skipped attributes
     *
     * @var array
     */
    public static $skippedAttributes = [
        'options',
        'created_at',
        'updated_at',
        'category_ids',
        'special_from_date',
        'news_from_date',
        'custom_design_from',
        'updated_in',
        'tax_class_id',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get('Magento\Framework\Filesystem');
        $this->model = $this->objectManager->create(
            'Magento\CatalogImportExport\Model\Export\Product'
        );
        $this->productResource = $this->objectManager->create(
            'Magento\Catalog\Model\ResourceModel\Product'
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @param array $fixtures
     * @param string[] $skus
     * @param string[] $skippedAttributes
     * @dataProvider exportImportDataProvider
     */
    public function testExport($fixtures, $skus, $skippedAttributes = [], $rollbackFixtures = [])
    {
        $this->executeFixtures($fixtures, $skus);
        $skippedAttributes = array_merge(self::$skippedAttributes, $skippedAttributes);
        $this->executeExportTest($skus, $skippedAttributes);
        $this->executeFixtures($rollbackFixtures);
    }

    protected function executeExportTest($skus, $skippedAttributes)
    {
        $index = 0;
        $ids = [];
        $origProducts = [];
        while (isset($skus[$index])) {
            $ids[$index] = $this->productResource->getIdBySku($skus[$index]);
            $origProducts[$index] = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($ids[$index]);
            $index++;
        }

        $csvfile = $this->exportProducts();
        $this->importProducts($csvfile, \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND);

        while ($index > 0) {
            $index--;
            $newProduct = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($ids[$index]);

            // @todo uncomment or remove after MAGETWO-49806 resolved
            //$this->assertEquals(count($origProductData[$index]), count($newProductData));

            $this->assertEqualsOtherThanSkippedAttributes(
                $origProducts[$index]->getData(),
                $newProduct->getData(),
                $skippedAttributes
            );

            $this->assertEqualsSpecificAttributes($origProducts[$index], $newProduct);
        }
    }

    private function assertEqualsOtherThanSkippedAttributes($expected, $actual, $skippedAttributes)
    {
        foreach ($expected as $key => $value) {
            if (is_object($value) || in_array($key, $skippedAttributes)) {
                continue;
            }

            $this->assertEquals(
                $value,
                $actual[$key],
                'Assert value at key - ' . $key . ' failed'
            );
        }
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @param array $fixtures
     * @param string[] $skus
     * @dataProvider exportImportDataProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testImportDelete($fixtures, $skus, $skippedAttributes = [], $rollbackFixtures = [])
    {
        $this->executeFixtures($fixtures, $skus);
        $this->executeImportDeleteTest($skus);
        $this->executeFixtures($rollbackFixtures);
    }

    protected function executeImportDeleteTest($skus)
    {
        $csvfile = $this->exportProducts();
        $this->importProducts($csvfile, \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE);
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        foreach ($skus as $sku) {
            $productId = $this->productResource->getIdBySku($sku);
            $product->load($productId);
            $this->assertNull($product->getId());
        }
    }

    /**
     * Execute fixtures
     *
     * @param array $skus
     * @param array $fixtures
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function executeFixtures($fixtures, $skus = [])
    {
        foreach ($fixtures as $fixture) {
            $fixturePath = $this->fileSystem->getDirectoryRead(DirectoryList::ROOT)
                ->getAbsolutePath('/dev/tests/integration/testsuite/' . $fixture);
            include $fixturePath;
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $expectedProduct
     * @param \Magento\Catalog\Model\Product $actualProduct
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function assertEqualsSpecificAttributes($expectedProduct, $actualProduct)
    {
        // check custom options
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @param array $fixtures
     * @param string[] $skus
     * @param string[] $skippedAttributes
     * @dataProvider importReplaceDataProvider
     */
    public function testImportReplace($fixtures, $skus, $skippedAttributes = [], $rollbackFixtures = [])
    {
        $this->executeFixtures($fixtures, $skus);
        $skippedAttributes = array_merge(self::$skippedAttributes, $skippedAttributes);
        $this->executeImportReplaceTest($skus, $skippedAttributes);
        $this->executeFixtures($rollbackFixtures);
    }

    protected function executeImportReplaceTest($skus, $skippedAttributes)
    {
        $replacedAttributes = [
            'row_id',
            'entity_id',
            'tier_price',
            'is_salable',
            'multiselect_attribute',
        ];
        $skippedAttributes = array_merge($replacedAttributes, $skippedAttributes);

        $index = 0;
        $ids = [];
        $origProducts = [];
        while (isset($skus[$index])) {
            $ids[$index] = $this->productResource->getIdBySku($skus[$index]);
            $origProducts[$index] = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($ids[$index]);
            $index++;
        }

        $csvfile = $this->exportProducts();
        $this->importProducts($csvfile, \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE);

        while ($index > 0) {
            $index--;

            $id = $this->productResource->getIdBySku($skus[$index]);
            $newProduct = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id);

            // check original product is deleted
            $origProduct = $this->objectManager->create('Magento\Catalog\Model\Product')->load($ids[$index]);
            $this->assertNull($origProduct->getId());

            // check new product data
            // @todo uncomment or remove after MAGETWO-49806 resolved
            //$this->assertEquals(count($origProductData[$index]), count($newProductData));

            $origProductData = $origProducts[$index]->getData();
            $newProductData = $newProduct->getData();
            $this->assertEqualsOtherThanSkippedAttributes($origProductData, $newProductData, $skippedAttributes);

            $this->assertEqualsSpecificAttributes($origProducts[$index], $newProduct);

            foreach ($replacedAttributes as $attribute) {
                if (isset($origProductData[$attribute]) && !empty($origProductData[$attribute])) {
                    $expected = $origProductData[$attribute];
                    $actual = isset($newProductData[$attribute]) ? $newProductData[$attribute] : null;
                    $this->assertNotEquals($expected, $actual, $attribute . ' is expected to be changed');
                }
            }
        }
    }

    /**
     * Export products in the system
     *
     * @return string Return exported file name
     */
    private function exportProducts()
    {
        $csvfile = uniqid('importexport_') . '.csv';

        $this->model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\ImportExport\Model\Export\Adapter\Csv',
                ['fileSystem' => $this->fileSystem, 'destination' => $csvfile]
            )
        );
        $this->assertNotEmpty($this->model->export());
        return $csvfile;
    }

    /**
     * Import products from the given file
     *
     * @param string $csvfile
     * @param string $behavior
     * @return void
     */
    private function importProducts($csvfile, $behavior)
    {
        /** @var \Magento\CatalogImportExport\Model\Import\Product $importModel */
        $importModel = $this->objectManager->create(
            'Magento\CatalogImportExport\Model\Import\Product'
        );
        $directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => $csvfile,
                'directory' => $directory
            ]
        );
        $errors = $importModel->setParameters(
            ['behavior' => $behavior, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0, 'Product import error, imported from file:' . $csvfile);
        $importModel->importData();
    }
}
