<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;

/**
 * @api
 * @since 2.0.0
 */
class ProductFieldset implements \Magento\Framework\Indexer\FieldsetInterface
{
    /**
     * @var Config
     * @since 2.0.0
     */
    private $eavConfig;

    /**
     * @var CollectionFactory
     * @since 2.0.0
     */
    private $collectionFactory;

    /**
     * @var Attribute[]
     * @since 2.0.0
     */
    private $searchableAttributes;
    
    /**
     * @param Config $eavConfig
     * @param CollectionFactory $collectionFactory
     * @since 2.0.0
     */
    public function __construct(
        Config $eavConfig,
        CollectionFactory $collectionFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function addDynamicData(array $data)
    {
        $searchableAttributes = $this->getSearchableAttributes();

        $defaultSource = isset($data['source']) ? $data['source'] : null;
        $additionalFields = $this->convert($searchableAttributes, $defaultSource, null);

        $data['fields'] = $this->merge($data['fields'], $additionalFields);

        return $data;
    }

    /**
     * Retrieve searchable attributes
     *
     * @return Attribute[]
     * @since 2.0.0
     */
    private function getSearchableAttributes()
    {
        if ($this->searchableAttributes === null) {
            $this->searchableAttributes = [];

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributes */
            $productAttributes = $this->collectionFactory->create();
            $productAttributes->addToIndexFilter(true);

            /** @var \Magento\Eav\Model\Entity\Attribute[] $attributes */
            $attributes = $productAttributes->getItems();

            $entity = $this->eavConfig->getEntityType(Product::ENTITY)
                ->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }

            $this->searchableAttributes = $attributes;
        }

        return $this->searchableAttributes;
    }

    /**
     * @param array $attributes
     * @param string $defaultSource
     * @param string $defaultHandler
     * @return array
     * @since 2.0.0
     */
    private function convert(array $attributes, $defaultSource, $defaultHandler)
    {
        $fields = [];
        foreach ($attributes as $attribute) {
            $fields[] = [
                'name' => $attribute->getName(),
                'source' => $defaultSource,
                'handler' => $defaultHandler,
                'dataType' => $attribute->getBackendType(),
                'type' => $this->getType($attribute),
                'filters' => [],
            ];
        }

        return $fields;
    }

    /**
     * @param Attribute $attribute
     * @return string
     * @since 2.0.0
     */
    private function getType(Attribute $attribute)
    {
        $type = '';
        $isFilterable = $attribute->getData('is_filterable') || $attribute->getData('is_filterable_in_search');
        $isSearchable = $attribute->getData('is_searchable');
        if ($isSearchable && $isFilterable) {
            $type = 'both';
        } elseif ($isSearchable) {
            $type = 'searchable';
        } elseif ($isFilterable) {
            $type = 'filterable';
        }

        return $type;
    }

    /**
     * @param array $dataFields
     * @param array $searchableFields
     * @return array
     * @since 2.0.0
     */
    private function merge(array $dataFields, array $searchableFields)
    {
        foreach ($searchableFields as $field) {
            if (!isset($dataFields[$field['name']])) {
                $dataFields[$field['name']] = $field;
            }
        }

        return $dataFields;
    }
}
