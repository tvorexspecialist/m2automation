<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Usps\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $configDataTable = $installer->getTable('core_config_data');
        $connection = $installer->getConnection();
        
        $oldToNewMethodCodesMap = [
            'First-Class' => '0_FCLE',
            'First-Class Mail International Large Envelope' => 'INT_14',
            'First-Class Mail International Letter' => 'INT_13',
            'First-Class Mail International Letters' => 'INT_13',
            'First-Class Mail International Package' => 'INT_15',
            'First-Class Mail International Parcel' => 'INT_13',
            'First-Class Package International Service' => 'INT_15',
            'First-Class Mail' => '0_FCLE',
            'First-Class Mail Flat' => '0_FCLE',
            'First-Class Mail Large Envelope' => '0_FCLE',
            'First-Class Mail International' => 'INT_14',
            'First-Class Mail Letter' => '0_FCL',
            'First-Class Mail Parcel' => '0_FCP',
            'First-Class Mail Package' => '0_FCP',
            'Parcel Post' => '4',
            'Retail Ground' => '4',
            'Media Mail' => '6',
            'Library Mail' => '7',
            'Express Mail' => '3',
            'Express Mail PO to PO' => '3',
            'Express Mail Flat Rate Envelope' => '13',
            'Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee' => '25',
            'Express Mail Sunday/Holiday Guarantee' => '23',
            'Express Mail Flat Rate Envelope Hold For Pickup' => '27',
            'Express Mail Hold For Pickup' => '2',
            'Global Express Guaranteed (GXG)' => 'INT_4',
            'Global Express Guaranteed Non-Document Rectangular' => 'INT_6',
            'Global Express Guaranteed Non-Document Non-Rectangular' => 'INT_7',
            'USPS GXG Envelopes' => 'INT_12',
            'Express Mail International' => 'INT_1',
            'Express Mail International Flat Rate Envelope' => 'INT_10',
            'Priority Mail' => '1',
            'Priority Mail Small Flat Rate Box' => '28',
            'Priority Mail Medium Flat Rate Box' => '17',
            'Priority Mail Large Flat Rate Box' => '22',
            'Priority Mail Flat Rate Envelope' => '16',
            'Priority Mail International' => 'INT_2',
            'Priority Mail International Flat Rate Envelope' => 'INT_8',
            'Priority Mail International Small Flat Rate Box' => 'INT_16',
            'Priority Mail International Medium Flat Rate Box' => 'INT_9',
            'Priority Mail International Large Flat Rate Box' => 'INT_11',
        ];
        
        $select = $connection->select()->from(
            $configDataTable
        )->where(
            'path IN (?)',
            ['carriers/usps/free_method', 'carriers/usps/allowed_methods']
        );
        $oldConfigValues = $connection->fetchAll($select);
        
        foreach ($oldConfigValues as $oldValue) {
            if (stripos($oldValue['path'], 'free_method') && isset($oldToNewMethodCodesMap[$oldValue['value']])) {
                $newValue = $oldToNewMethodCodesMap[$oldValue['value']];
            } elseif (stripos($oldValue['path'], 'allowed_methods')) {
                $newValuesList = [];
                foreach (explode(',', $oldValue['value']) as $shippingMethod) {
                    if (isset($oldToNewMethodCodesMap[$shippingMethod])) {
                        $newValuesList[] = $oldToNewMethodCodesMap[$shippingMethod];
                    }
                }
                $newValue = implode(',', $newValuesList);
            } else {
                continue;
            }
        
            if ($newValue && $newValue != $oldValue['value']) {
                $whereConfigId = $connection->quoteInto('config_id = ?', $oldValue['config_id']);
                $connection->update($configDataTable, ['value' => $newValue], $whereConfigId);
            }
        }
        
    }
}
