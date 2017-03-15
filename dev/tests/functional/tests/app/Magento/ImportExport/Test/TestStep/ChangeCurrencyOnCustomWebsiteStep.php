<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\TestStep;

use Magento\ImportExport\Test\Fixture\ImportData;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Create custom store step.
 */
class ChangeCurrencyOnCustomWebsiteStep implements TestStepInterface
{
    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Import fixture.
     *
     * @var ImportData
     */
    private $import;

    /**
     * Change currency flag.
     *
     * @var bool
     */
    private $changeCurrency;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param ImportData $import
     * @param bool|null $changeCurrency
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        ImportData $import,
        $changeCurrency
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->import = $import;
        $this->changeCurrency = $changeCurrency;
    }

    /**
     * Fill import form.
     *
     * @return void
     */
    public function run()
    {
        if ($this->changeCurrency === true) {
            $currency = $this->import->getDataFieldConfig('import_file')['source']
                ->getValue()['template']['websiteCurrency'];
            $entities = $this->import->getDataFieldConfig('import_file')['source']->getEntities();
            foreach ($entities as $entity) {
                $websites = $entity->getDataFieldConfig('website_ids')['source']->getWebsites();
                $configFixture = $this->fixtureFactory->createByCode(
                    'configData',
                    [
                        'data' => [
                            'currency/options/allow' => [
                                'value' => [$currency]
                            ],
                            'currency/options/base' => [
                                'value' => $currency
                            ],
                            'currency/options/default' => [
                                'value' => $currency
                            ],
                            'scope' => [
                                'fixture' => $websites[0],
                                'scope_type' => 'website',
                                'website_id' => $websites[0]->getWebsiteId(),
                                'set_level' => 'website',
                            ]
                        ]
                    ]
                );
                $configFixture->persist();
            }
        }
    }
}
