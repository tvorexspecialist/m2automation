<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorComposite;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeRepository implements \Magento\Eav\Api\AttributeRepositoryInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavResource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var Entity\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $joinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param Config $eavConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory
     * @param Entity\AttributeFactory $attributeFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavResource = $eavResource;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->attributeFactory = $attributeFactory;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Eav\Api\Data\AttributeInterface $attribute)
    {
        try {
            $this->eavResource->save($attribute);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot save attribute'));
        }
        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($entityTypeCode, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        if (!$entityTypeCode) {
            throw InputException::requiredField('entity_type_code');
        }

        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributeCollection */
        $attributeCollection = $this->attributeCollectionFactory->create();
        $this->joinProcessor->process($attributeCollection);
        $attributeCollection->addFieldToFilter('entity_type_code', ['eq' => $entityTypeCode]);
        $attributeCollection->join(
            ['entity_type' => $attributeCollection->getTable('eav_entity_type')],
            'main_table.entity_type_id = entity_type.entity_type_id',
            []
        );
        $attributeCollection->joinLeft(
            ['eav_entity_attribute' => $attributeCollection->getTable('eav_entity_attribute')],
            'main_table.attribute_id = eav_entity_attribute.attribute_id',
            []
        );
        $entityType = $this->eavConfig->getEntityType($entityTypeCode);

        $additionalTable = $entityType->getAdditionalAttributeTable();
        if ($additionalTable) {
            $attributeCollection->join(
                ['additional_table' => $attributeCollection->getTable($additionalTable)],
                'main_table.attribute_id = additional_table.attribute_id',
                []
            );
        }

        $this->collectionProcessor->process($searchCriteria, $attributeCollection);

        // Group attributes by id to prevent duplicates with different attribute sets
        $attributeCollection->addAttributeGrouping();

        $attributes = [];
        /** @var \Magento\Eav\Api\Data\AttributeInterface $attribute */
        foreach ($attributeCollection as $attribute) {
            $attributes[] = $this->get($entityTypeCode, $attribute->getAttributeCode());
        }

        /** @var \Magento\Eav\Api\Data\AttributeSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($attributes);
        $searchResults->setTotalCount($attributeCollection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function get($entityTypeCode, $attributeCode)
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface $attribute */
        $attribute = $this->eavConfig->getAttribute($entityTypeCode, $attributeCode);
        if (!$attribute || !$attribute->getAttributeId()) {
            throw new NoSuchEntityException(
                __('Attribute with attributeCode "%1" does not exist.', $attributeCode)
            );
        }
        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Eav\Api\Data\AttributeInterface $attribute)
    {
        try {
            $this->eavResource->delete($attribute);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot delete attribute.'));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($attributeId)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        $attribute = $this->attributeFactory->create();
        $this->eavResource->load($attribute, $attributeId);

        if (!$attribute->getAttributeId()) {
            throw new NoSuchEntityException(__('Attribute with id "%1" does not exist.', $attributeId));
        }

        $this->delete($attribute);
        return true;
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                CollectionProcessorComposite::class
            );
        }
        return $this->collectionProcessor;
    }
}
