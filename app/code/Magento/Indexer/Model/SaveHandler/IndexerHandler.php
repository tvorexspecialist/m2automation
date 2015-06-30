<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\SaveHandler;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\IndexerInterface;
use Magento\Indexer\Model\IndexStructure;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Search\Model\ScopeResolver\FlatScopeResolver;
use Magento\Search\Model\ScopeResolver\IndexScopeResolver;

class IndexerHandler implements IndexerInterface
{
    /**
     * @var string[]
     */
    private $dataTypes = ['searchable', 'filterable'];

    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var Resource|Resource
     */
    private $resource;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var IndexScopeResolverInterface[]
     */
    private $scopeResolvers;

    /**
     * @param IndexStructure $indexStructure
     * @param Resource $resource
     * @param Batch $batch
     * @param IndexScopeResolver $indexScopeResolver
     * @param FlatScopeResolver $flatScopeResolver
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        IndexStructure $indexStructure,
        Resource $resource,
        Batch $batch,
        IndexScopeResolver $indexScopeResolver,
        FlatScopeResolver $flatScopeResolver,
        array $data,
        $batchSize = 100
    ) {
        $this->indexStructure = $indexStructure;
        $this->resource = $resource;
        $this->batch = $batch;
        $this->scopeResolvers[$this->dataTypes[0]] = $indexScopeResolver;
        $this->scopeResolvers[$this->dataTypes[1]] = $flatScopeResolver;
        $this->data = $data;
        $this->batchSize = $batchSize;

        $this->fields = [];
        $this->prepareFields();
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            foreach ($this->dataTypes as $dataType) {
                $this->insertDocuments($dataType, $batchDocuments, $dimensions);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->dataTypes as $dataType) {
            foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
                $documentsId = array_column($batchDocuments, 'id');
                $this->getAdapter()->delete($this->getTableName($dataType, $dimensions), ['id' => $documentsId]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexName(), $dimensions);
        $this->indexStructure->create($this->getIndexName(), $this->fields, $dimensions);
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @param string $dataType
     * @param Dimension[] $dimensions
     * @return string
     */
    private function getTableName($dataType, $dimensions)
    {
        return $this->scopeResolvers[$dataType]->resolve($this->getIndexName(), $dimensions);
    }

    /**
     * @return string
     */
    private function getIndexName()
    {
        return $this->data['indexer_id'];
    }

    /**
     * @return AdapterInterface
     */
    private function getAdapter()
    {
        return $this->resource->getConnection(Resource::DEFAULT_WRITE_RESOURCE);
    }

    /**
     * @param string $dataType
     * @param array $documents
     * @param Dimension[] $dimensions
     * @return void
     */
    private function insertDocuments($dataType, array $documents, array $dimensions)
    {
        if ($dataType === $this->dataTypes[0]) {
            $documents = $this->prepareSearchableFields($documents);
            $updater = ['data_index'];
        } else {
            $documents = $this->prepareFilterableFields($documents);
            $updater = [];
            foreach ($this->fields as $field) {
                $updater[] = $field['name'];
            }
        }
        $this->getAdapter()->insertOnDuplicate(
            $this->getTableName($dataType, $dimensions),
            $documents,
            $updater
        );
    }

    /**
     * @param array $documents
     * @return array
     */
    private function prepareFilterableFields(array $documents)
    {
        $insertDocuments = [];
        foreach ($documents as $entityId => $document) {
            $documentFlat = ['entity_id' => $entityId];
            foreach ($this->fields as $field) {
                $documentFlat[$field['name']] = $document[$field['name']];
            }
            $insertDocuments[] = $documentFlat;
        }
        return $insertDocuments;
    }

    /**
     * @param array $documents
     * @return array
     */
    private function prepareSearchableFields(array $documents)
    {
        $insertDocuments = [];
        foreach ($documents as $entityId => $document) {
            foreach ($this->fields as $field) {
                $insertDocuments[] = [
                    'entity_id' => $entityId,
                    'attribute_id' => $field['name'],
                    'data_index' => $document[$field['name']],
                ];
            }
        }

        return $insertDocuments;
    }

    /**
     * @return void
     */
    private function prepareFields()
    {
        foreach ($this->data['fieldsets'] as $fieldset) {
            $this->fields = array_merge($this->fields, $fieldset['fields']);
        }
    }
}
