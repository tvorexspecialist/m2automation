<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Block;

use Magento\Cms\Model\Resource\Block\Collection;
use Magento\Cms\Model\Resource\Block\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;

/**
 * Class DataProvider
 */
class DataProvider implements DataProviderInterface
{
    /**
     * Data Provider name
     *
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $primaryFieldName;

    /**
     * @var string
     */
    protected $requestFieldName;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * Provider configuration data
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        FilterPool $filterPool,
        array $meta = [],
        array $data = []
    ) {
        $this->name = $name;
        $this->primaryFieldName = $primaryFieldName;
        $this->requestFieldName = $requestFieldName;
        $this->collection = $collectionFactory->create();
        $this->meta = $meta;
        $this->data = $data;
    }

    /**
     * Get Data Provider name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get primary field name
     *
     * @return string
     */
    public function getPrimaryFieldName()
    {
        return $this->primaryFieldName;
    }

    /**
     * Get field name in request
     *
     * @return string
     */
    public function getRequestFieldName()
    {
        return $this->requestFieldName;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Get field Set meta info
     *
     * @param string $fieldSetName
     * @return array
     */
    public function getFieldSetMetaInfo($fieldSetName)
    {
        return isset($this->meta[$fieldSetName]) ? $this->meta[$fieldSetName] : [];
    }

    /**
     * @param string $fieldSetName
     * @return array
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return isset($this->meta[$fieldSetName]['fields']) ? $this->meta[$fieldSetName]['fields'] : [];
    }

    /**
     * @param string $fieldSetName
     * @param string $fieldName
     * @return array
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return isset($this->meta[$fieldSetName]['fields'][$fieldName])
            ? $this->meta[$fieldSetName]['fields'][$fieldName]
            : [];
    }

    /**
     * @inheritdoc
     */
    /**
     * @inheritdoc
     */
    public function addFilter($condition, $field = null,  $type = 'regular')
    {
        $this->filterPool->registerNewFilter($condition, $field, $type);
    }


    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        $this->collection->addFieldToSelect($field, $alias);
    }

    /**
     * Add ORDER BY to the end or to the beginning
     *
     * @param string $field
     * @param string $direction
     * @return void
     */
    public function addOrder($field, $direction)
    {
        $this->collection->addOrder($field, $direction);
    }

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function setLimit($offset, $size)
    {
        $this->collection->setPageSize($size);
        $this->collection->setCurPage($offset);
    }

    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     */
    public function removeField($field, $isAlias = false)
    {
        $this->collection->removeFieldFromSelect($field, $isAlias);
    }

    /**
     * Removes all fields from select
     *
     * @return void
     */
    public function removeAllFields()
    {
        $this->collection->removeAllFieldsFromSelect();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $this->filterPool->applyFilters($this->collection);
        return $this->collection->toArray();
    }

    /**
     * Retrieve count of loaded items
     *
     * @return int
     */
    public function count()
    {
        $this->filterPool->applyFilters($this->collection);
        return $this->collection->count();
    }

    /**
     * Get config data
     *
     * @return mixed
     */
    public function getConfigData()
    {
        return isset($this->data['config']) ? $this->data['config'] : [];
    }

    /**
     * Set data
     *
     * @param mixed $config
     * @return void
     */
    public function setConfigData($config)
    {
        $this->data['config'] = $config;
    }
}
