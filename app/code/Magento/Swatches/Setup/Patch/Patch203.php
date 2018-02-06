<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Setup\Patch;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch203 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory = null
     */
    private $fieldDataConverterFactory = null;

    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory = null
     */
    public function __construct(FieldDataConverterFactory $fieldDataConverterFactory = null)
    {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();

        $this->convertAddDataToJson($setup);


        $setup->endSetup();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


    private function convertAddDataToJson(ModuleDataSetupInterface $setup
    )
    {
        $fieldConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $fieldConverter->convert(
            $setup->getConnection(),
            $setup->getTable('catalog_eav_attribute'),
            'attribute_id',
            'additional_data'
        );

    }
}
