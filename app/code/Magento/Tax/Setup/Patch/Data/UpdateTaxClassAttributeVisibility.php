<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;
use Magento\Tax\Setup\TaxSetup;
use Magento\Tax\Setup\TaxSetupFactory;

/**
 * Class UpdateTaxClassAttributeVisibility
 * @package Magento\Tax\Setup\Patch
 */
class UpdateTaxClassAttributeVisibility implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var TaxSetupFactory
     */
    private $taxSetupFactory;

    /**
     * UpdateTaxClassAttributeVisibility constructor.
     * @param ResourceConnection $resourceConnection
     * @param TaxSetupFactory $taxSetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TaxSetupFactory $taxSetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->taxSetupFactory = $taxSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var TaxSetup $taxSetup */
        $taxSetup = $this->taxSetupFactory->create(['resourceName' => 'tax_setup']);

        $this->resourceConnection->getConnection()->startSetup();

         //Update the tax_class_id attribute in the 'catalog_eav_attribute' table
        $taxSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'tax_class_id',
            'is_visible_in_advanced_search',
            false
        );
        $this->resourceConnection->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddTacAttributeAndTaxClasses::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
