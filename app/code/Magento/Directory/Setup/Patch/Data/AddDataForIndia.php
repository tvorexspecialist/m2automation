<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class AddDataForIndia
 * @package Magento\Directory\Setup\Patch\Data
 */
class AddDataForIndia implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Directory\Setup\DataInstallerFactory
     */
    private $dataInstallerFactory;

    /**
     * AddDataForCroatia constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Directory\Setup\DataInstallerFactory $dataInstallerFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Directory\Setup\DataInstallerFactory $dataInstallerFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dataInstallerFactory = $dataInstallerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var DataInstaller $dataInstaller */
        $dataInstaller = $this->dataInstallerFactory->create();
        $dataInstaller->addCountryRegions(
            $this->resourceConnection->getConnection(),
            $this->getDataForIndia()
        );
    }

    /**
     * Indian states data.
     *
     * @return array
     */
    private function getDataForIndia()
    {
        return [
            ['IN', 'AN', 'Andaman and Nicobar Islands'],
            ['IN', 'AP', 'Andhra Pradesh'],
            ['IN', 'AR', 'Arunachal Pradesh'],
            ['IN', 'AS', 'Assam'],
            ['IN', 'BR', 'Bihar'],
            ['IN', 'CH', 'Chandigarh'],
            ['IN', 'CT', 'Chhattisgarh'],
            ['IN', 'DN', 'Dadra and Nagar Haveli'],
            ['IN', 'DD', 'Daman and Diu'],
            ['IN', 'DL', 'Delhi'],
            ['IN', 'GA', 'Goa'],
            ['IN', 'GJ', 'Gujarat'],
            ['IN', 'HR', 'Haryana'],
            ['IN', 'HP', 'Himachal Pradesh'],
            ['IN', 'JK', 'Jammu and Kashmir'],
            ['IN', 'JH', 'Jharkhand'],
            ['IN', 'KA', 'Karnataka'],
            ['IN', 'KL', 'Kerala'],
            ['IN', 'LD', 'Lakshadweep'],
            ['IN', 'MP', 'Madhya Pradesh'],
            ['IN', 'MH', 'Maharashtra'],
            ['IN', 'MN', 'Manipur'],
            ['IN', 'ML', 'Meghalaya'],
            ['IN', 'MZ', 'Mizoram'],
            ['IN', 'NL', 'Nagaland'],
            ['IN', 'OR', 'Odisha'],
            ['IN', 'PY', 'Puducherry'],
            ['IN', 'PB', 'Punjab'],
            ['IN', 'RJ', 'Rajasthan'],
            ['IN', 'SK', 'Sikkim'],
            ['IN', 'TN', 'Tamil Nadu'],
            ['IN', 'TG', 'Telangana'],
            ['IN', 'TR', 'Tripura'],
            ['IN', 'UP', 'Uttar Pradesh'],
            ['IN', 'UT', 'Uttarakhand'],
            ['IN', 'WB', 'West Bengal']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            InitializeDirectoryData::class,
        ];
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
