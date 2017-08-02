<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav;

/**
 * EAV attribute indexer in catalog
 *
 * @api
 * @since 2.0.0
 */
class Processor extends \Magento\Framework\Indexer\AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = 'catalog_product_attribute';
}
