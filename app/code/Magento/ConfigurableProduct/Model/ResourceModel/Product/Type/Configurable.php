<?php
/**
 * Configurable product type resource model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ScopeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock\Status as StockStatus;
use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;
use Magento\Catalog\Model\Product as ProductModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Catalog product relation
     *
     * @var ProductRelation
     */
    protected $catalogProductRelation;

    /**
     * Product metadata pool
     *
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /** @var ScopeResolverInterface  */
    private $scopeResolver;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param DbContext $context
     * @param ProductRelation $catalogProductRelation
     * @param string $connectionName
     * @param ScopeResolverInterface $scopeResolver
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        DbContext $context,
        ProductRelation $catalogProductRelation,
        $connectionName = null,
        ScopeResolverInterface $scopeResolver = null,
        StockRegistryInterface $stockRegistry = null
    ) {
        $this->catalogProductRelation = $catalogProductRelation;
        $this->scopeResolver = $scopeResolver;
        $this->stockRegistry = $stockRegistry ?: ObjectManager::getInstance()->get(StockRegistryInterface::class);
        parent::__construct($context, $connectionName);
    }

    /**
     * Init resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_super_link', 'link_id');
    }

    /**
     * Get product entity id by product attribute
     *
     * @param OptionInterface $option
     * @return int
     */
    public function getEntityIdByAttribute(OptionInterface $option)
    {
        $select = $this->getConnection()->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['e.entity_id']
        )->where(
            'e.' . $this->getProductEntityLinkField() . '=?',
            $option->getProductId()
        )->limit(1);

        return (int) $this->getConnection()->fetchOne($select);
    }

    /**
     * Save configurable product relations
     *
     * @param ProductModel $mainProduct the parent id
     * @param array $productIds the children id array
     * @return $this
     */
    public function saveProducts($mainProduct, array $productIds)
    {
        if (!$mainProduct instanceof ProductInterface) {
            return $this;
        }

        $productId = $mainProduct->getData($this->getProductEntityLinkField());

        $data = [];
        foreach ($productIds as $id) {
            $data[] = ['product_id' => (int) $id, 'parent_id' => (int) $productId];
        }

        if (!empty($data)) {
            $this->getConnection()->insertOnDuplicate(
                $this->getMainTable(),
                $data,
                ['product_id', 'parent_id']
            );
        }

        $where = ['parent_id = ?' => $productId];
        if (!empty($productIds)) {
            $where['product_id NOT IN(?)'] = $productIds;
        }

        $this->getConnection()->delete($this->getMainTable(), $where);

        // configurable product relations should be added to relation table
        $this->catalogProductRelation->processRelations($productId, $productIds);

        return $this;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int|array $parentId
     * @param bool $required
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getChildrenIds($parentId, $required = true)
    {
        $select = $this->getConnection()->select()->from(
            ['l' => $this->getMainTable()],
            ['product_id', 'parent_id']
        )->join(
            ['p' => $this->getTable('catalog_product_entity')],
            'p.' . $this->getProductEntityLinkField() . ' = l.parent_id',
            []
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = l.product_id AND e.required_options = 0',
            []
        )->where(
            'p.entity_id IN (?)',
            $parentId
        );

        $childrenIds = [0 => []];
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $childrenIds[0][$row['product_id']] = $row['product_id'];
        }

        return $childrenIds;
    }

    /**
     * Retrieve parent ids array by required child
     *
     * @param int|array $childId
     * @return string[]
     */
    public function getParentIdsByChild($childId)
    {
        $parentIds = [];
        $select = $this->getConnection()
            ->select()
            ->from(['l' => $this->getMainTable()], [])
            ->join(
                ['e' => $this->getTable('catalog_product_entity')],
                'e.' . $this->getProductEntityLinkField() . ' = l.parent_id',
                ['e.entity_id']
            )->where('l.product_id IN(?)', $childId);

        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $parentIds[] = $row['entity_id'];
        }

        return $parentIds;
    }

    /**
     * Collect product options with values according to the product instance and attributes, that were received
     *
     * @param ProductModel $product
     * @param array $attributes
     * @return array
     */
    public function getConfigurableOptions($product, $attributes)
    {
        $attributesOptionsData = [];
        $productId = $product->getData($this->getProductEntityLinkField());
        foreach ($attributes as $superAttribute) {
            $attributeId = $superAttribute->getAttributeId();
            $attributesOptionsData[$attributeId] = $this->getAttributeOptions($superAttribute, $productId);
        }
        return $attributesOptionsData;
    }

    /**
     * Load options for attribute
     *
     * @param AbstractAttribute $superAttribute
     * @param int $productId
     * @return array
     */
    public function getAttributeOptions(AbstractAttribute $superAttribute, $productId)
    {
        $scope  = $this->getScopeResolver()->getScope();
        $select = $this->getAttributeOptionsSelect($superAttribute, $productId, $scope);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Load in stock options for attribute
     *
     * @param AbstractAttribute $superAttribute
     * @param int $productId
     * @return array
     */
    public function getInStockAttributeOptions(AbstractAttribute $superAttribute, $productId)
    {
        $scope  = $this->getScopeResolver()->getScope();
        $select = $this->getAttributeOptionsSelect($superAttribute, $productId, $scope);
        $options = $this->getConnection()->fetchAll($select);
        // workaround as we do not have bulk method to get all statuses by id
        foreach ($options as $key => $option) {
            $status = $this->stockRegistry->getProductStockStatusBySku($option['sku']);
            if ($status !== null && $status == StockStatus::STATUS_OUT_OF_STOCK) {
                unset($options[$key]);
            }
        }
        $options = array_values($options);

        return $options;
    }

    /**
     * Get load options for attribute select
     *
     * @param AbstractAttribute $superAttribute
     * @param int $productId
     * @param ScopeInterface $scope
     * @return Select
     */
    private function getAttributeOptionsSelect(AbstractAttribute $superAttribute, $productId, ScopeInterface $scope)
    {
        $select = $this->getConnection()->select()->from(
            ['super_attribute' => $this->getTable('catalog_product_super_attribute')],
            [
                'sku' => 'entity.sku',
                'product_id' => 'product_entity.entity_id',
                'attribute_code' => 'attribute.attribute_code',
                'value_index' => 'entity_value.value',
                'option_title' => $this->getConnection()->getIfNullSql(
                    'option_value.value',
                    'default_option_value.value'
                ),
                'default_title' => 'default_option_value.value',
            ]
        )->joinInner(
            ['product_entity' => $this->getTable('catalog_product_entity')],
            "product_entity.{$this->getProductEntityLinkField()} = super_attribute.product_id",
            []
        )->joinInner(
            ['product_link' => $this->getTable('catalog_product_super_link')],
            'product_link.parent_id = super_attribute.product_id',
            []
        )->joinInner(
            ['attribute' => $this->getTable('eav_attribute')],
            'attribute.attribute_id = super_attribute.attribute_id',
            []
        )->joinInner(
            ['entity' => $this->getTable('catalog_product_entity')],
            'entity.entity_id = product_link.product_id',
            []
        )->joinInner(
            ['entity_value' => $superAttribute->getBackendTable()],
            implode(
                ' AND ',
                [
                    'entity_value.attribute_id = super_attribute.attribute_id',
                    'entity_value.store_id = 0',
                    "entity_value.{$this->getProductEntityLinkField()} = "
                    . "entity.{$this->getProductEntityLinkField()}"
                ]
            ),
            []
        )->joinLeft(
            ['option_value' => $this->getTable('eav_attribute_option_value')],
            implode(
                ' AND ',
                [
                    'option_value.option_id = entity_value.value',
                    'option_value.store_id = ' . $scope->getId()
                ]
            ),
            []
        )->joinLeft(
            ['default_option_value' => $this->getTable('eav_attribute_option_value')],
            implode(
                ' AND ',
                [
                    'default_option_value.option_id = entity_value.value',
                    'default_option_value.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID
                ]
            ),
            []
        )->where(
            'super_attribute.product_id = ?',
            $productId
        )->where(
            'attribute.attribute_id = ?',
            $superAttribute->getAttributeId()
        );

        return $select;
    }

    /**
     * @deprecated
     * @return ScopeResolverInterface
     */
    private function getScopeResolver()
    {
        if (!($this->scopeResolver instanceof ScopeResolverInterface)) {
            $this->scopeResolver = ObjectManager::getInstance()->get(ScopeResolverInterface::class);
        }
        return $this->scopeResolver;
    }

    /**
     * Get product metadata pool
     *
     * @deprecated
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()
                ->get(MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * Get product entity link field
     *
     * @deprecated
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }
}
