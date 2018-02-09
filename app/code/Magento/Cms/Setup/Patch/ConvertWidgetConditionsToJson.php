<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Setup\Patch;

use Magento\Cms\Setup\ContentConverter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\VersionedDataPatch;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Widget\Setup\LayoutUpdateConverter;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;

/**
 * Class ConvertWidgetConditionsToJson
 * @package Magento\Cms\Setup\Patch
 */
class ConvertWidgetConditionsToJson implements DataPatchInterface, VersionedDataPatch
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldDataConverter;

    /**
     * ConvertWidgetConditionsToJson constructor.
     * @param ResourceConnection $resourceConnection
     * @param QueryModifierFactory $queryModifierFactory
     * @param MetadataPool $metadataPool
     * @param AggregatedFieldDataConverter $aggregatedFieldDataConverter
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        QueryModifierFactory $queryModifierFactory,
        MetadataPool $metadataPool,
        AggregatedFieldDataConverter $aggregatedFieldDataConverter
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->metadataPool = $metadataPool;
        $this->aggregatedFieldDataConverter = $aggregatedFieldDataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $queryModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'content' => '%conditions_encoded%'
                ]
            ]
        );
        $layoutUpdateXmlFieldQueryModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'layout_update_xml' => '%conditions_encoded%'
                ]
            ]
        );
        $customLayoutUpdateXmlFieldQueryModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'custom_layout_update_xml' => '%conditions_encoded%'
                ]
            ]
        );
        $blockMetadata = $this->metadataPool->getMetadata(BlockInterface::class);
        $pageMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $this->aggregatedFieldDataConverter->convert(
            [
                new FieldToConvert(
                    ContentConverter::class,
                    $this->resourceConnection->getConnection()->getTableName('cms_block'),
                    $blockMetadata->getIdentifierField(),
                    'content',
                    $queryModifier
                ),
                new FieldToConvert(
                    ContentConverter::class,
                    $this->resourceConnection->getConnection()->getTableName('cms_page'),
                    $pageMetadata->getIdentifierField(),
                    'content',
                    $queryModifier
                ),
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $this->resourceConnection->getConnection()->getTableName('cms_page'),
                    $pageMetadata->getIdentifierField(),
                    'layout_update_xml',
                    $layoutUpdateXmlFieldQueryModifier
                ),
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $this->resourceConnection->getConnection()->getTableName('cms_page'),
                    $pageMetadata->getIdentifierField(),
                    'custom_layout_update_xml',
                    $customLayoutUpdateXmlFieldQueryModifier
                ),
            ],
            $this->resourceConnection->getConnection()
        );

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
    public function getVersion()
    {
        return '2.0.2';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
