<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Setup\Patch;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial
{


    /**
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    private $quoteSetupFactory;
    /**
     * @param SalesSetupFactory $salesSetupFactory
     */
    private $salesSetupFactory;
    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @param QuoteSetupFactory $quoteSetupFactory @param SalesSetupFactory $salesSetupFactory@param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(QuoteSetupFactory $quoteSetupFactory,
                                SalesSetupFactory $salesSetupFactory

        ,
                                CategorySetupFactory $categorySetupFactory)
    {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * Add 'gift_message_id' attributes for entities
         */
        $options = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'visible' => false, 'required' => false];
        $entities = ['quote', 'quote_address', 'quote_item', 'quote_address_item'];
        /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'gift_message_id', $options);
        }

        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $salesSetup->addAttribute('order', 'gift_message_id', $options);
        $salesSetup->addAttribute('order_item', 'gift_message_id', $options);
        /**
         * Add 'gift_message_available' attributes for entities
         */
        $salesSetup->addAttribute('order_item', 'gift_message_available', $options);
        /** @var \Magento\Catalog\Setup\CategorySetup $catalogSetup */
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $setup]);
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

}
