<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Fixture\Import;

use Magento\ImportExport\Mtf\Util\Import\File\CsvTemplate;
use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Util\Generate\File\Generator;

/**
 * Fixture of file.
 */
class File extends DataSource
{
    /**
     * Website code mapping.
     *
     * @var array
     */
    private $mainWebsiteMapping;

    /**
     * Fixture data.
     *
     * @var array
     */
    private $value;

    /**
     * Template of csv file.
     *
     * @var array
     */
    private $csvTemplate;

    /**
     * Generator for the uploaded file.
     *
     * @var Generator
     */
    private $generator;

    /**
     * Factory for fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Entities fixtures.
     *
     * @var FixtureInterface[]
     */
    private $entities;

    /**
     * Object manager.
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Csv as array.
     *
     * @var array
     */
    private $csv;

    /**
     * Placeholders.
     *
     * @var array
     */
    private $placeholders;

    /**
     * @param ObjectManager $objectManager
     * @param FixtureFactory $fixtureFactory
     * @param Generator $generator
     * @param array $params
     * @param array|string $data
     */
    public function __construct(
        ObjectManager $objectManager,
        FixtureFactory $fixtureFactory,
        Generator $generator,
        array $params,
        $data = []
    ) {
        $this->params = $params;
        $this->value = $data;
        $this->generator = $generator;
        $this->fixtureFactory = $fixtureFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key = null)
    {
        if (isset($this->data)) {
            return parent::getData($key);
        }

        $filename = MTF_TESTS_PATH . $this->value['template']['filename'] . '.php';
        if (!file_exists($filename)) {
            throw new \Exception("CSV file '{$filename}'' not found on the server.");
        }

        $this->csvTemplate = include $filename;

        $this->placeholders = [];
        if (!isset($this->products)
            && isset($this->value['entities'])
            && is_array($this->value['entities'])
        ) {
            $this->entities = $this->createEntities();
            $this->preparePlaceHolders();
        }

        if (isset($this->value['template']) && is_array($this->value['template'])) {
            $csvTemplate = $this->objectManager->create(
                CsvTemplate::class,
                [
                    'config' => array_merge(
                        $this->value['template'],
                        [
                            'placeholders' => $this->placeholders
                        ]
                    )
                ]
            );
            $this->data = $this->generator->generate($csvTemplate);
            $this->convertCsvToArray($csvTemplate->getCsv());

            return parent::getData($key);
        }

        $filename = MTF_TESTS_PATH . $this->value;
        if (!file_exists($filename)) {
            throw new \Exception("CSV file '{$filename}'' not found on the server.");
        }

        $this->data = $filename;

        return parent::getData($key);
    }

    /**
     * Get entities fixtures.
     *
     * @return FixtureInterface[]
     */
    public function getEntities()
    {
        return $this->entities ?: [];
    }

    /**
     * Get fixture data.
     *
     * @return array|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Create entities from configuration of variation.
     *
     * @return FixtureInterface[]
     */
    private function createEntities()
    {
        $entities = [];
        foreach ($this->value['entities'] as $key => $entityDataSet) {
            list($fixtureCode, $dataset) = explode('::', $entityDataSet);

            /** @var FixtureInterface[] $entities */
            $entities[$key] = $this->fixtureFactory->createByCode(trim($fixtureCode), ['dataset' => trim($dataset)]);
            if ($entities[$key]->hasData('id') === false) {
                $entities[$key]->persist();
            }
        }
        ksort($entities);
        return $entities;
    }

    /**
     * Create placeholders for entities.
     *
     * @return void
     */
    private function preparePlaceHolders()
    {
        $placeholders = [];
        $key = 0;
        foreach ($this->entities as $entity) {
            $entityData = $this->prepareEntityData($entity);
            foreach ($this->csvTemplate['entity_' . $key] as $entityKey => $importedEntityData) {
                $values = implode('', array_values($importedEntityData));
                preg_match_all('/\%(.*)\%/U', $values, $indexes);
                foreach ($indexes[1] as $index) {
                    if (isset($entityData[$index])) {
                        $placeholders['entity_' . $key][$entityKey]["%{$index}%"] = $entityData[$index];
                    }
                    if (isset($entityData['code'])) {
                        $placeholders['entity_' . $key][$entityKey][$entityData['code']]
                            = isset($entityData[$entityData['code']])
                            ? $entityData[$entityData['code']]
                            : 'Main Website';
                    }
                }
            }
            $key++;
        }
        $this->placeholders = $placeholders;
    }

    /**
     * Prepare entity data.
     *
     * @param FixtureInterface $entity
     * @return array
     */
    public function prepareEntityData(FixtureInterface $entity)
    {
        $currency = (isset($this->value['template']['websiteCurrency']))
            ? "[{$this->value['template']['websiteCurrency']}]"
            : '[USD]';
        $entityData = $entity->getData();
        if (isset($entityData['quantity_and_stock_status'])) {
            $entityData = array_merge($entityData, $entityData['quantity_and_stock_status']);
        }
        if (isset($entityData['website_ids'])) {
            $websites = $entity->getDataFieldConfig('website_ids')['source']->getWebsites();
            foreach ($websites as $website) {
                if ($website->getCode() === 'base') {
                    $currency = isset($this->value['template']['mainWebsiteCurrency'])
                        ? $this->value['template']['mainWebsiteCurrency']
                        : '[USD]';
                    $this->mainWebsiteMapping['base'] = $website->getName() . "[{$currency}]";
                    break;
                }
                $entityData['code'] = $website->getCode();
                $entityData[$website->getCode()] = $website->getName() . $currency;
            }
        }
        if ($entity->getDataConfig() && ('simple' !== $entity->getDataConfig()['type_id'])) {
            $class = ucfirst($entity->getDataConfig()['type_id']);
            $file = ObjectManager::getInstance()->create("\\Magento\\{$class}ImportExport\\Test\\Fixture\\Import\\File");
            $entityData = $file->getData($entity, $this->fixtureFactory);
        }
        return $entityData;
    }

    /**
     * Prepare bundle product data.
     *
     * @param FixtureInterface $product
     * @return array
     */
    private function getBundleProductData(FixtureInterface $product)
    {
        $newProduct = $this->fixtureFactory->createByCode('catalogProductSimple', ['dataset' => 'default']);
        $newProduct->persist();
        $newProductData = $newProduct->getData();
        $productData = $product->getData();

        $productData['bundle_attribute_sku'] = $newProductData['sku'];
        $productData['bundle_attribute_name'] = $newProductData['name'];
        $productData['bundle_attribute_url_key'] = $newProductData['url_key'];

        return $productData;
    }
    /**
     * Prepare grouped product data.
     *
     * @param FixtureInterface $product
     * @return array
     */
    private function getGroupedProductData(FixtureInterface $product)
    {
        $newProduct = $this->fixtureFactory->createByCode('catalogProductSimple', ['dataset' => 'default']);
        $newProduct->persist();
        $newProductData = $newProduct->getData();
        $productData = $product->getData();

        $productData['grouped_associated_skus'] = $newProductData['sku'];
        $productData['grouped_attribute_sku'] = $newProductData['sku'];
        $productData['grouped_attribute_name'] = $newProductData['name'];
        $productData['grouped_attribute_url_key'] = $newProductData['url_key'];
        return $productData;
    }

    /**
     * Prepare configurable product data.
     *
     * @param FixtureInterface $product
     * @return array
     */
    private function getConfigurableProductData(FixtureInterface $product)
    {
        $newProduct = $this->fixtureFactory->createByCode('configurableProduct', ['dataset' => 'with_one_attribute']);
        $newProduct->persist();
        $newProductData = $newProduct->getData();
        $newAttributeData = $newProductData['configurable_attributes_data']['matrix']['attribute_key_0:option_key_0'];
        $productData = $product->getData();

        $productData['configurable_attribute_sku'] = $newAttributeData['sku'];
        $productData['configurable_attribute_name'] = $newAttributeData['name'];
        $productData['configurable_attribute_url_key'] = str_replace('_', '-', $newAttributeData['sku']);
        $productData['configurable_additional_attributes'] =
            $newProductData['configurable_attributes_data']['attributes_data']['attribute_key_0']['frontend_label'];

        return $productData;
    }


    /**
     * Convert csv to array.
     *
     * @param string $csvContent
     * @return array
     */
    public function convertCsvToArray($csvContent)
    {
        foreach ($this->placeholders as $placeholderData) {
            foreach ($placeholderData as $data) {
                $csvContent = strtr($csvContent, $data);
            }
        }
        if (is_array($this->mainWebsiteMapping)) {
            $csvContent = strtr($csvContent, $this->mainWebsiteMapping);
        }
        $this->csv = array_map(
            function ($value) {
                $explodedArray = explode(",", $value);
                $count = count($explodedArray);
                for ($i = 0; $i < $count; $i++) {
                    if (preg_match('/^\".*[^"]$/U', $explodedArray[$i])) {
                        $implodedKey = $i;
                        while ((++$i <= $count) && !preg_match('/^[^"].*\"$/U', $explodedArray[$i])) {
                            $explodedArray[$implodedKey] .= ',' . $explodedArray[$i];
                            $explodedArray[$i] = '%%deleted%%';
                        }
                        $explodedArray[$implodedKey] .= ',' . $explodedArray[$i];
                        $explodedArray[$i] = '%%deleted%%';
                        $explodedArray[$implodedKey] = str_replace('"', '', $explodedArray[$implodedKey]);
                    } else {
                        $explodedArray[$i] = str_replace('"', '', $explodedArray[$i]);
                    };
                }
                return array_diff($explodedArray, ['%%deleted%%']);
            },
            str_getcsv($csvContent, "\n")
        );
    }

    /**
     * Return csv as array.
     *
     * @return array
     */
    public function getCsv()
    {
        return $this->csv;
    }
}
