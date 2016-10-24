<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel\Selection;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\DataObject;

/**
 * Bundle Selections Resource Collection
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Selection table name
     *
     * @var string
     */
    protected $_selectionTable;

    /**
     * @var DataObject
     */
    private $itemPrototype = null;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Product\Collection
     */
    private $catalogRuleProcessor;

    /**
     * @inheritDoc
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        \Magento\CatalogRule\Model\ResourceModel\Product\Collection $catalogRuleProcessor,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );
        $this->catalogRuleProcessor = $catalogRuleProcessor;
    }

    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setRowIdFieldName('selection_id');
        $this->_selectionTable = $this->getTable('catalog_product_bundle_selection');
    }

    /**
     * Set store id for each collection item when collection was loaded
     *
     * @return $this
     */
    public function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getStoreId() && $this->_items) {
            foreach ($this->_items as $item) {
                $item->setStoreId($this->getStoreId());
            }
        }
        return $this;
    }

    /**
     * Initialize collection select
     *
     * @return $this|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->join(
            ['selection' => $this->_selectionTable],
            'selection.product_id = e.entity_id',
            ['*']
        );
    }

    /**
     * Join website scope prices to collection, override default prices
     *
     * @param int $websiteId
     * @return $this
     */
    public function joinPrices($websiteId)
    {
        $connection = $this->getConnection();
        $priceType = $connection->getCheckSql(
            'price.selection_price_type IS NOT NULL',
            'price.selection_price_type',
            'selection.selection_price_type'
        );
        $priceValue = $connection->getCheckSql(
            'price.selection_price_value IS NOT NULL',
            'price.selection_price_value',
            'selection.selection_price_value'
        );
        $this->getSelect()->joinLeft(
            ['price' => $this->getTable('catalog_product_bundle_selection_price')],
            'selection.selection_id = price.selection_id AND price.website_id = ' . (int)$websiteId,
            [
                'selection_price_type' => $priceType,
                'selection_price_value' => $priceValue,
                'price_scope' => 'price.website_id'
            ]
        );
        return $this;
    }

    /**
     * Apply option ids filter to collection
     *
     * @param array $optionIds
     * @return $this
     */
    public function setOptionIdsFilter($optionIds)
    {
        if (!empty($optionIds)) {
            $this->getSelect()->where('selection.option_id IN (?)', $optionIds);
        }
        return $this;
    }

    /**
     * Apply selection ids filter to collection
     *
     * @param array $selectionIds
     * @return $this
     */
    public function setSelectionIdsFilter($selectionIds)
    {
        if (!empty($selectionIds)) {
            $this->getSelect()->where('selection.selection_id IN (?)', $selectionIds);
        }
        return $this;
    }

    /**
     * Set position order
     *
     * @return $this
     */
    public function setPositionOrder()
    {
        $this->getSelect()->order('selection.position asc')->order('selection.selection_id asc');
        return $this;
    }

    /**
     * Add filtering of product then havent enoght stock
     *
     * @return $this
     */
    public function addQuantityFilter()
    {
        $this->getSelect()
            ->joinInner(
                ['stock' => $this->getTable('cataloginventory_stock_status')],
                'selection.product_id = stock.product_id',
                []
            )
            ->where(
                '(selection.selection_can_change_qty or selection.selection_qty <= stock.qty) and stock.stock_status'
            );
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNewEmptyItem()
    {
        if ($this->itemPrototype == null) {
            $this->itemPrototype = parent::getNewEmptyItem();
        }
        return clone $this->itemPrototype;
    }

    /**
     * @param Product $product
     * @param bool $searchMin
     * @param bool $useRegularPrice
     */
    public function addPriceFilter($product, $searchMin, $useRegularPrice = false)
    {
        if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $this->addPriceData();
            if ($useRegularPrice) {
                $minimalPriceExpression = 'minimal_price';
            } else {
                $this->catalogRuleProcessor->addPriceData($this, 'selection.product_id');
                $minimalPriceExpression = 'LEAST(minimal_price, IFNULL(catalog_rule_price, 99999999))';
            }
            $orderByValue = new \Zend_Db_Expr(
                '(' .
                $minimalPriceExpression .
                ' * selection.selection_qty' .
                ')'
            );
        } else {
            $connection = $this->getConnection();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $priceType = $connection->getIfNullSql(
                'price.selection_price_type',
                'selection.selection_price_type'
            );
            $priceValue = $connection->getIfNullSql(
                'price.selection_price_value',
                'selection.selection_price_value'
            );
            $this->getSelect()->joinLeft(
                ['price' => $this->getTable('catalog_product_bundle_selection_price')],
                'selection.selection_id = price.selection_id AND price.website_id = ' . (int)$websiteId,
                []
            );
            $price = $connection->getCheckSql(
                $priceType . ' = 1',
                (float) $product->getPrice() . ' * '. $priceValue . ' / 100',
                $priceValue
            );
            $orderByValue = new \Zend_Db_Expr('('. $price. ' * '. 'selection.selection_qty)');
        }

        $this->getSelect()->order($orderByValue . ($searchMin ? \Zend_Db_Select::SQL_ASC : \Zend_Db_Select::SQL_DESC));
        $this->getSelect()->limit(1);
    }
}
