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
     * skipped attributes
     *
     * @var array
     */
    public static $skippedAttributes = [
        'options',
        'updated_at',
        'extension_attributes',
        'category_ids',
        'special_from_date',
        'news_from_date',
        'weight',
        'custom_design_from',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get('Magento\Framework\Filesystem');
        $this->model = $this->objectManager->create(
            'Magento\CatalogImportExport\Model\Export\Product'
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
        $this->executeFixtures($fixtures);
        $skippedAttributes = array_merge(self::$skippedAttributes, $skippedAttributes);
        $this->executeExportTest($skus, $skippedAttributes);
        $this->executeFixtures($rollbackFixtures);
    }

    protected function executeExportTest($skus, $skippedAttributes)
    {
        $productRepository = $this->objectManager->create(
            'Magento\Catalog\Api\ProductRepositoryInterface'
        );
        $index = 0;
        $ids = [];
        $origProductData = [];
        while (isset($skus[$index])) {
            $ids[$index] = $productRepository->get($skus[$index])->getEntityId();
            $origProductData[$index] = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($ids[$index])
                ->getData();
            $index++;
        }

        $csvfile = uniqid('importexport_') . '.csv';

        $this->model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\ImportExport\Model\Export\Adapter\Csv',
                ['fileSystem' => $this->fileSystem, 'destination' => $csvfile]
            )
        );
        $this->assertNotEmpty($this->model->export());

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
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0, 'Product import error, imported from file:' . $csvfile);
        $importModel->importData();

        while ($index > 0) {
            $index--;
            $newProductData = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($ids[$index])
                ->getData();
            $this->assertEquals(count($origProductData[$index]), count($newProductData));
            $this->assertEqualsOtherThanSkippedAttributes(
                $origProductData[$index],
                $newProductData,
                $skippedAttributes
            );
        }
    }

    private function assertEqualsOtherThanSkippedAttributes($expected, $actual, $skippedAttributes)
    {
        foreach ($expected as $key => $value) {
            if (in_array($key, $skippedAttributes)) {
                continue;
            } else {
                $this->assertEquals(
                    $value,
                    $actual[$key],
                    'Assert value at key - ' . $key . ' failed'
                );
            }
        }
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @param array $fixture
     * @param string[] $skus
     * @dataProvider exportImportDataProvider
     */
    public function testImportDelete($fixtures, $skus, $skippedAttributes = [], $rollbackFixtures = [])
    {
        $this->executeFixtures($fixtures);
        $this->executeImportDeleteTest($skus);
        $this->executeFixtures($rollbackFixtures);
    }

    protected function executeImportDeleteTest($skus)
    {
        $defaultProductData = $this->objectManager->create('Magento\Catalog\Model\Product')
            ->load(100)
            ->getData();
        $productRepository = $this->objectManager->create(
            'Magento\Catalog\Api\ProductRepositoryInterface'
        );
        $index = 0;
        $ids = [];
        $origProductData = [];
        while (isset($skus[$index])) {
            $ids[$index] = $productRepository->get($skus[$index])->getEntityId();
            $origProductData[$index] = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($ids[$index])
                ->getData();
            $index++;
        }

        $csvfile = uniqid('importdelete_') . '.csv';

        $this->model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\ImportExport\Model\Export\Adapter\Csv',
                ['fileSystem' => $this->fileSystem, 'destination' => $csvfile]
            )
        );
        $this->assertNotEmpty($this->model->export());

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
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0, 'Product import error, imported from file:' . $csvfile);
        $importModel->importData();

        while ($index > 0) {
            $index--;
            $newProduct = $this->objectManager->create('Magento\Catalog\Model\Product')->load($ids[$index]);
            $newProductData = $newProduct->getData();
            $this->assertEquals($defaultProductData, $newProductData);
        }
    }

    /**
     * Execute fixtures
     *
     * @param array $fixtures
     * @return void
     */
    private function executeFixtures($fixtures)
    {
        foreach ($fixtures as $fixture) {
            $fixturePath = $this->fileSystem->getDirectoryRead(DirectoryList::ROOT)
                ->getAbsolutePath('/dev/tests/integration/testsuite/' . $fixture);
            include $fixturePath;
        }
    }
}
