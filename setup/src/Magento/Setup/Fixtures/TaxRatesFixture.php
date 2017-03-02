<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Class TaxRatesFixture
 */
class TaxRatesFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 100;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $taxRatesFile = $this->fixtureModel->getValue('tax_rates_file', null);
        if (empty($taxRatesFile)) {
            return;
        }
        $this->fixtureModel->resetObjectManager();
        /** Clean predefined tax rates to maintain consistency */
        /** @var $collection \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection */
        $collection = $this->fixtureModel->getObjectManager()
            ->get(\Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection::class);

        /** @var $model \Magento\Tax\Model\Calculation\Rate */
        $model = $this->fixtureModel->getObjectManager()
            ->get(\Magento\Tax\Model\Calculation\Rate::class);

        foreach ($collection->getAllIds() as $id) {
            $model->setId($id);
            $model->delete();
        }
        /**
         * Import tax rates with import handler
         */
        $filename = realpath(__DIR__ . DIRECTORY_SEPARATOR . "_files" . DIRECTORY_SEPARATOR . $taxRatesFile);
        $file = [
            'name' => $filename,
            'type' => 'fixtureModel/vnd.ms-excel',
            'tmp_name' => $filename,
            'error' => 0,
            'size' => filesize($filename),
        ];
        $importHandler = $this->fixtureModel->getObjectManager()
            ->create(\Magento\TaxImportExport\Model\Rate\CsvImportHandler::class);
        $importHandler->importFromCsvFile($file);

    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating tax rates';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [];
    }
}
