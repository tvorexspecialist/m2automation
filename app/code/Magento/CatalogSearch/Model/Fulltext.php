<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Search\Model\QueryFactory;

/**
 * Catalog advanced search model
 *
 * @method \Magento\CatalogSearch\Model\ResourceModel\Fulltext _getResource()
 * @method \Magento\CatalogSearch\Model\ResourceModel\Fulltext getResource()
 * @method int getProductId()
 * @method \Magento\CatalogSearch\Model\Fulltext setProductId(int $value)
 * @method int getStoreId()
 * @method \Magento\CatalogSearch\Model\Fulltext setStoreId(int $value)
 * @method string getDataIndex()
 * @method \Magento\CatalogSearch\Model\Fulltext setDataIndex(string $value)
 * @since 2.0.0
 */
class Fulltext extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Catalog search data
     *
     * @var QueryFactory
     * @since 2.0.0
     */
    protected $queryFactory = null;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param QueryFactory $queryFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param AbstractResource $resource
     * @param DbCollection $resourceCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Registry $registry,
        QueryFactory $queryFactory,
        ScopeConfigInterface $scopeConfig,
        AbstractResource $resource = null,
        DbCollection $resourceCollection = null,
        array $data = []
    ) {
        $this->queryFactory = $queryFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\CatalogSearch\Model\ResourceModel\Fulltext::class);
    }

    /**
     * Reset search results cache
     *
     * @return $this
     * @since 2.0.0
     */
    public function resetSearchResults()
    {
        $this->getResource()->resetSearchResults();
        return $this;
    }
}
