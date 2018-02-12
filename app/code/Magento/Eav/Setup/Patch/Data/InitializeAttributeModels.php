<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class InitializeAttributeModels
 * @package Magento\Eav\Setup\Patch
 */
class InitializeAttributeModels implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * InitializeAttributeModels constructor.
     * @param ResourceConnection $resourceConnection
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        /** @var \Magento\Framework\Module\Setup\Migration $migrationSetup */
        $migrationSetup = $this->moduleDataSetup->createMigrationSetup();

        $migrationSetup->appendClassAliasReplace(
            'eav_attribute',
            'attribute_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_attribute',
            'backend_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_attribute',
            'frontend_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_attribute',
            'source_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_entity_type',
            'entity_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['entity_type_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_entity_type',
            'attribute_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['entity_type_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_entity_type',
            'increment_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['entity_type_id']
        );
        $migrationSetup->appendClassAliasReplace(
            'eav_entity_type',
            'entity_attribute_collection',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_RESOURCE,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['entity_type_id']
        );
        $migrationSetup->doUpdateClassAliases();
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create([
            'setup' => $this->moduleDataSetup,
            'resourceConnection' => $this->resourceConnection
        ]);
        $groups = $eavSetup->getAttributeGroupCollectionFactory();
        foreach ($groups as $group) {
            /** @var $group \Magento\Eav\Model\Entity\Attribute\Group*/
            $group->save();
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
