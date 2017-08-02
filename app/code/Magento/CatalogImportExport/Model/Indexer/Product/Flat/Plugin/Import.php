<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Indexer\Product\Flat\Plugin;

/**
 * Class \Magento\CatalogImportExport\Model\Indexer\Product\Flat\Plugin\Import
 *
 * @since 2.0.0
 */
class Import
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     * @since 2.0.0
     */
    protected $_productFlatIndexerProcessor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor)
    {
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
    }

    /**
     * After import handler
     *
     * @param \Magento\ImportExport\Model\Import $subject
     * @param Object $import
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterImportSource(\Magento\ImportExport\Model\Import $subject, $import)
    {
        $this->_productFlatIndexerProcessor->markIndexerAsInvalid();
        return $import;
    }
}
