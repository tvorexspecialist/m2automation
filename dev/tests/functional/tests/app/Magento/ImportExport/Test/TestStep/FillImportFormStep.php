<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\TestStep;

use Magento\ImportExport\Test\Fixture\Import\File;
use Magento\ImportExport\Test\Fixture\ImportData;
use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Fill import form.
 */
class FillImportFormStep implements TestStepInterface
{
    /**
     * Import index page.
     *
     * @var AdminImportIndex
     */
    private $adminImportIndex;

    /**
     * Import fixture.
     *
     * @var ImportData
     */
    private $import;

    /**
     * Csv as array.
     *
     * @var array
     */
    private $csv;

    /**
     * @param AdminImportIndex $adminImportIndex
     * @param ImportData $import
     * @param TestStepFactory $stepFactory
     * @param bool $createStore
     */
    public function __construct(
        AdminImportIndex $adminImportIndex,
        ImportData $import,
        TestStepFactory $stepFactory,
        $createStore
    ) {
        $file = $import->getDataFieldConfig('import_file')['source'];
        $this->adminImportIndex = $adminImportIndex;
        $this->import = $import;
        $this->csv = $file->getCsv();

        if ($createStore === true) {
            $this->csv = $stepFactory->create(
                CreateCustomStoreStep::class,
                ['products' => $file->getProducts(), 'csv' => $this->csv]
            )->run();
        }
    }

    /**
     * Fill import form.
     *
     * @return array
     */
    public function run()
    {
        $this->adminImportIndex->getImportForm()->fill($this->import);

        /** @var File $file */
        $file = $this->import->getDataFieldConfig('import_file')['source'];

        return [
            'products' => $file->getProducts(),
            'csv' => $this->csv,
            'behavior' => $this->import->getBehavior()
        ];
    }
}
