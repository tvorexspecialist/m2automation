<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media;
use Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoMediaGalleryEntryProcessor;

/**
 * Class InstallSchema adds new table `eav_attribute_option_swatch`
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $contextInterface)
    {
        $setup->startSetup();

        /**
         * Create table 'catalog_product_entity_media_gallery_value_video'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable(ExternalVideoMediaGalleryEntryProcessor::GALLERY_VALUE_VIDEO_TABLE))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Media Entity ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'provider',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => false],
                'Video provider ID'
            )
            ->addColumn(
                'url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Video URL'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Title'
            )
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'Page Meta Description'
            )
            ->addColumn(
                'metadata',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'Video meta data'
            )
            ->addIndex(
                $setup->getIdxName(ExternalVideoMediaGalleryEntryProcessor::GALLERY_VALUE_VIDEO_TABLE, ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    ExternalVideoMediaGalleryEntryProcessor::GALLERY_VALUE_VIDEO_TABLE,
                    'value_id',
                    Media::GALLERY_TABLE,
                    'value_id'
                ),
                'value_id',
                $setup->getTable(Media::GALLERY_TABLE),
                'value_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Product Video Table');

        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }
}
