<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export\Product\Type;

/**
 * Export entity product type simple model
 *
 * @api
 * @since 2.0.0
 */
class Simple extends \Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType
{
    /**
     * Overridden attributes parameters.
     *
     * @var array
     * @since 2.0.0
     */
    protected $_attributeOverrides = [
        'has_options' => ['source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class],
        'required_options' => ['source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class],
        'created_at' => ['backend_type' => 'datetime'],
        'updated_at' => ['backend_type' => 'datetime'],
    ];

    /**
     * Array of attributes codes which are disabled for export.
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_disabledAttrs = [
        'old_id',
        'tier_price',
        'category_ids',
        'has_options',
        'is_returnable',
        'required_options',
        'quantity_and_stock_status'
    ];
}
