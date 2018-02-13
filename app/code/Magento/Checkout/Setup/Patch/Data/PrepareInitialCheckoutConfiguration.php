<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class PrepareInitialCheckoutConfiguration
 * @package Magento\Checkout\Setup\Patch
 */
class PrepareInitialCheckoutConfiguration implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    private $customerAddress;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        \Magento\Customer\Helper\Address $customerAddress
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerAddress = $customerAddress;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $this->moduleDataSetup->getConnection()->startSetup();

        $connection = $this->moduleDataSetup->getConnection();

        $select = $connection->select()->from(
            $connection->getTableName('core_config_data'),
            'COUNT(*)'
        )->where(
            'path=?',
            'customer/address/prefix_show'
        )->where(
            'value NOT LIKE ?',
            '0'
        );
        $showPrefix = (bool)$this->customerAddress->getConfig('prefix_show')
            || $connection->fetchOne($select) > 0;

        $select = $connection->select()->from(
            $connection->getTableName('core_config_data'),
            'COUNT(*)'
        )->where(
            'path=?',
            'customer/address/middlename_show'
        )->where(
            'value NOT LIKE ?',
            '0'
        );
        $showMiddlename = (bool)$this->customerAddress->getConfig('middlename_show')
            || $connection->fetchOne($select) > 0;

        $select = $connection->select()->from(
            $connection->getTableName('core_config_data'),
            'COUNT(*)'
        )->where(
            'path=?',
            'customer/address/suffix_show'
        )->where(
            'value NOT LIKE ?',
            '0'
        );
        $showSuffix = (bool)$this->customerAddress->getConfig('suffix_show')
            || $connection->fetchOne($select) > 0;

        $select = $connection->select()->from(
            $connection->getTableName('core_config_data'),
            'COUNT(*)'
        )->where(
            'path=?',
            'customer/address/dob_show'
        )->where(
            'value NOT LIKE ?',
            '0'
        );
        $showDob = (bool)$this->customerAddress->getConfig('dob_show')
            || $connection->fetchOne($select) > 0;

        $select = $connection->select()->from(
            $connection->getTableName('core_config_data'),
            'COUNT(*)'
        )->where(
            'path=?',
            'customer/address/taxvat_show'
        )->where(
            'value NOT LIKE ?',
            '0'
        );
        $showTaxVat = (bool)$this->customerAddress->getConfig('taxvat_show')
            || $connection->fetchOne($select) > 0;

        $customerEntityTypeId = $eavSetup->getEntityTypeId('customer');
        $addressEntityTypeId = $eavSetup->getEntityTypeId('customer_address');

        /**
         *****************************************************************************
         * checkout/onepage/register
         *****************************************************************************
         */

        $connection->insert(
            $connection->getTableName('eav_form_type'),
            [
                'code' => 'checkout_onepage_register',
                'label' => 'checkout_onepage_register',
                'is_system' => 1,
                'theme' => '',
                'store_id' => 0
            ]
        );
        $formTypeId = $connection->lastInsertId($connection->getTableName('eav_form_type'));

        $connection->insert(
            $connection->getTableName('eav_form_type_entity'),
            ['type_id' => $formTypeId, 'entity_type_id' => $customerEntityTypeId]
        );
        $connection->insert(
            $connection->getTableName('eav_form_type_entity'),
            ['type_id' => $formTypeId, 'entity_type_id' => $addressEntityTypeId]
        );

        $elementSort = 0;
        if ($showPrefix) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'prefix'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'firstname'),
                'sort_order' => $elementSort++
            ]
        );
        if ($showMiddlename) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'middlename'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'lastname'),
                'sort_order' => $elementSort++
            ]
        );
        if ($showSuffix) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'suffix'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'company'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($customerEntityTypeId, 'email'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'street'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'city'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'region'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'postcode'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'country_id'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'telephone'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'fax'),
                'sort_order' => $elementSort++
            ]
        );
        if ($showDob) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($customerEntityTypeId, 'dob'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        if ($showTaxVat) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($customerEntityTypeId, 'taxvat'),
                    'sort_order' => $elementSort++
                ]
            );
        }

        /**
         *****************************************************************************
         * checkout/onepage/register_guest
         *****************************************************************************
         */

        $connection->insert(
            $connection->getTableName('eav_form_type'),
            [
                'code' => 'checkout_onepage_register_guest',
                'label' => 'checkout_onepage_register_guest',
                'is_system' => 1,
                'theme' => '',
                'store_id' => 0
            ]
        );
        $formTypeId = $connection->lastInsertId($connection->getTableName('eav_form_type'));

        $connection->insert(
            $connection->getTableName('eav_form_type_entity'),
            ['type_id' => $formTypeId, 'entity_type_id' => $customerEntityTypeId]
        );
        $connection->insert(
            $connection->getTableName('eav_form_type_entity'),
            ['type_id' => $formTypeId, 'entity_type_id' => $addressEntityTypeId]
        );

        $elementSort = 0;
        if ($showPrefix) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'prefix'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'firstname'),
                'sort_order' => $elementSort++
            ]
        );
        if ($showMiddlename) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'middlename'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'lastname'),
                'sort_order' => $elementSort++
            ]
        );
        if ($showSuffix) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'suffix'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'company'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($customerEntityTypeId, 'email'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'street'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'city'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'region'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'postcode'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'country_id'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'telephone'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'fax'),
                'sort_order' => $elementSort++
            ]
        );
        if ($showDob) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($customerEntityTypeId, 'dob'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        if ($showTaxVat) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($customerEntityTypeId, 'taxvat'),
                    'sort_order' => $elementSort++
                ]
            );
        }

        /**
         *****************************************************************************
         * checkout/onepage/billing_address
         *****************************************************************************
         */

        $connection->insert(
            $connection->getTableName('eav_form_type'),
            [
                'code' => 'checkout_onepage_billing_address',
                'label' => 'checkout_onepage_billing_address',
                'is_system' => 1,
                'theme' => '',
                'store_id' => 0
            ]
        );
        $formTypeId = $connection->lastInsertId($connection->getTableName('eav_form_type'));

        $connection->insert(
            $connection->getTableName('eav_form_type_entity'),
            ['type_id' => $formTypeId, 'entity_type_id' => $addressEntityTypeId]
        );

        $elementSort = 0;
        if ($showPrefix) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'prefix'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'firstname'),
                'sort_order' => $elementSort++
            ]
        );
        if ($showMiddlename) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'middlename'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'lastname'),
                'sort_order' => $elementSort++
            ]
        );
        if ($showSuffix) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'suffix'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'company'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'street'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'city'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'region'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'postcode'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'country_id'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'telephone'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'fax'),
                'sort_order' => $elementSort++
            ]
        );

        /**
         *****************************************************************************
         * checkout/onepage/shipping_address
         *****************************************************************************
         */

        $connection->insert(
            $connection->getTableName('eav_form_type'),
            [
                'code' => 'checkout_onepage_shipping_address',
                'label' => 'checkout_onepage_shipping_address',
                'is_system' => 1,
                'theme' => '',
                'store_id' => 0
            ]
        );
        $formTypeId = $connection->lastInsertId($connection->getTableName('eav_form_type'));

        $connection->insert(
            $connection->getTableName('eav_form_type_entity'),
            ['type_id' => $formTypeId, 'entity_type_id' => $addressEntityTypeId]
        );

        $elementSort = 0;
        if ($showPrefix) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'prefix'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'firstname'),
                'sort_order' => $elementSort++
            ]
        );
        if ($showMiddlename) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'middlename'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'lastname'),
                'sort_order' => $elementSort++
            ]
        );
        if ($showSuffix) {
            $connection->insert(
                $connection->getTableName('eav_form_element'),
                [
                    'type_id' => $formTypeId,
                    'fieldset_id' => null,
                    'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'suffix'),
                    'sort_order' => $elementSort++
                ]
            );
        }
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'company'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'street'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'city'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'region'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'postcode'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'country_id'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'telephone'),
                'sort_order' => $elementSort++
            ]
        );
        $connection->insert(
            $connection->getTableName('eav_form_element'),
            [
                'type_id' => $formTypeId,
                'fieldset_id' => null,
                'attribute_id' => $eavSetup->getAttributeId($addressEntityTypeId, 'fax'),
                'sort_order' => $elementSort++
            ]
        );

        $table = $connection->getTableName('core_config_data');

        $select = $connection->select()->from(
            $table,
            ['config_id', 'value']
        )->where(
            'path = ?',
            'checkout/options/onepage_checkout_disabled'
        );

        $data = $connection->fetchAll($select);

        if ($data) {
            try {
                $connection->beginTransaction();

                foreach ($data as $value) {
                    $bind = ['path' => 'checkout/options/onepage_checkout_enabled', 'value' => !(bool)$value['value']];
                    $where = 'config_id = ' . $value['config_id'];
                    $connection->update($table, $bind, $where);
                }

                $connection->commit();
            } catch (\Exception $e) {
                $connection->rollback();
                throw $e;
            }
        }

        $connection->endSetup();
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
