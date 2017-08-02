<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleImportExport\Model\Import\Product\Type\Bundle;

/**
 * A bundle product relations (options, selections, etc.) data saver.
 *
 * Performs saving of a bundle product relations data during import operations.
 * @since 2.2.0
 */
class RelationsDataSaver
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.2.0
     */
    private $resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Saves given options.
     *
     * @param array $options
     *
     * @return void
     * @since 2.2.0
     */
    public function saveOptions(array $options)
    {
        if (!empty($options)) {
            $this->resource->getConnection()->insertOnDuplicate(
                $this->resource->getTableName('catalog_product_bundle_option'),
                $options,
                [
                    'required',
                    'position',
                    'type'
                ]
            );
        }
    }

    /**
     * Saves given option values.
     *
     * @param array $optionValues
     *
     * @return void
     * @since 2.2.0
     */
    public function saveOptionValues(array $optionValues)
    {
        if (!empty($optionValues)) {
            $this->resource->getConnection()->insertOnDuplicate(
                $this->resource->getTableName('catalog_product_bundle_option_value'),
                $optionValues,
                ['title']
            );
        }
    }

    /**
     * Saves given selections.
     *
     * @param array $selections
     *
     * @return void
     * @since 2.2.0
     */
    public function saveSelections(array $selections)
    {
        if (!empty($selections)) {
            $this->resource->getConnection()->insertOnDuplicate(
                $this->resource->getTableName('catalog_product_bundle_selection'),
                $selections,
                [
                    'selection_id',
                    'product_id',
                    'position',
                    'is_default',
                    'selection_price_type',
                    'selection_price_value',
                    'selection_qty',
                    'selection_can_change_qty'
                ]
            );
        }
    }
}
