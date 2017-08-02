<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\App\Config\MutableScopeConfigInterface as ScopeConfig;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Store\Model\Store;

/**
 * Cron operation is responsible for deleting all product prices on WEBSITE level
 * in case 'Catalog Price Scope' configuratoin parameter is set to GLOBAL.
 * @since 2.1.3
 */
class DeleteOutdatedPriceValues
{
    /**
     * @var ResourceConnection
     * @since 2.1.3
     */
    private $resource;

    /**
     * @var AttributeRepository
     * @since 2.1.3
     */
    private $attributeRepository;

    /**
     * @var ScopeConfig
     * @since 2.1.3
     */
    private $scopeConfig;

    /**
     * @param ResourceConnection $resource
     * @param AttributeRepository $attributeRepository
     * @param ScopeConfig $scopeConfig
     * @since 2.1.3
     */
    public function __construct(
        ResourceConnection $resource,
        AttributeRepository $attributeRepository,
        ScopeConfig $scopeConfig
    ) {
        $this->resource = $resource;
        $this->attributeRepository = $attributeRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Delete all price values for non-admin stores if PRICE_SCOPE is global
     *
     * @return void
     * @since 2.1.3
     */
    public function execute()
    {
        $priceScope = $this->scopeConfig->getValue(Store::XML_PATH_PRICE_SCOPE);
        if ($priceScope == Store::PRICE_SCOPE_GLOBAL) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $priceAttribute */
            $priceAttribute = $this->attributeRepository
                ->get(ProductAttributeInterface::ENTITY_TYPE_CODE, ProductAttributeInterface::CODE_PRICE);
            $connection = $this->resource->getConnection();
            $conditions = [
                $connection->quoteInto('attribute_id = ?', $priceAttribute->getId()),
                $connection->quoteInto('store_id != ?', Store::DEFAULT_STORE_ID),
            ];

            $connection->delete(
                $priceAttribute->getBackend()->getTable(),
                $conditions
            );
        }
    }
}
