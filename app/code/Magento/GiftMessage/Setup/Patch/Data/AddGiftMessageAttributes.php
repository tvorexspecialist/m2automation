<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

class AddGiftMessageAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * AddGiftMessageAttributes constructor.
     * @param ResourceConnection $resourceConnection
     * @param CategorySetupFactory $categorySetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CategorySetupFactory $categorySetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /**
         * Add 'gift_message_id' attributes for entities
         */
        $options = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'visible' => false, 'required' => false];
        $entities = ['quote', 'quote_address', 'quote_item', 'quote_address_item'];
        /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['resourceConnection' => $this->resourceConnection]);
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'gift_message_id', $options);
        }

        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['resourceConnection' => $this->resourceConnection]);
        $salesSetup->addAttribute('order', 'gift_message_id', $options);
        $salesSetup->addAttribute('order_item', 'gift_message_id', $options);
        /**
         * Add 'gift_message_available' attributes for entities
         */
        $salesSetup->addAttribute('order_item', 'gift_message_available', $options);
        /** @var \Magento\Catalog\Setup\CategorySetup $catalogSetup */
        $catalogSetup = $this->categorySetupFactory->create(['resourceConnection' => $this->resourceConnection]);
        $catalogSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'gift_message_available',
            [
                'group' => 'Gift Options',
                'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Boolean::class,
                'frontend' => '',
                'label' => 'Allow Gift Message',
                'input' => 'select',
                'class' => '',
                'source' => \Magento\Catalog\Model\Product\Attribute\Source\Boolean::class,
                'global' => true,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'apply_to' => '',
                'input_renderer' => \Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config::class,
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );
        $groupName = 'Autosettings';
        $entityTypeId = $catalogSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $catalogSetup->getAttributeSetId($entityTypeId, 'Default');
        $attribute = $catalogSetup->getAttribute($entityTypeId, 'gift_message_available');
        if ($attribute) {
            $catalogSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $groupName,
                $attribute['attribute_id'],
                60
            );
        }
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
