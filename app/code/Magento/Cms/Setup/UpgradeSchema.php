<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Setup;


use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $connection = $installer->getConnection();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $connection->addIndex(
                'cms_page',
                $setup->getIdxName(
                    'cms_page',
                    ['title', 'meta_keywords', 'meta_description', 'identifier'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['title', 'meta_keywords', 'meta_description', 'identifier'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
            $connection->addIndex(
                'cms_block',
                $setup->getIdxName('cms_block', ['title', 'identifier'], AdapterInterface::INDEX_TYPE_FULLTEXT),
                ['title', 'identifier'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
    }
}
