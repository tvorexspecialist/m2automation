<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Setup\Patch\Data;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class UpgradePasswordHashes
 * @package Magento\User\Setup\Patch
 */
class UpgradePasswordHashes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * PatchInitial constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->startSetup();
        $this->upgradeHash();
        $this->resourceConnection->getConnection()->endSetup();
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
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Upgrade password hashes.
     */
    private function upgradeHash()
    {
        $connection = $this->resourceConnection->getConnection();
        $customerEntityTable = $connection->getTableName('admin_user');

        $select = $connection->select()->from(
            $customerEntityTable,
            ['user_id', 'password']
        );

        $customers = $connection->fetchAll($select);
        foreach ($customers as $customer) {
            list($hash, $salt) = explode(Encryptor::DELIMITER, $customer['password']);

            $newHash = $customer['password'];
            if (strlen($hash) === 32) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_MD5]);
            } elseif (strlen($hash) === 64) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_SHA256]);
            }

            $bind = ['password' => $newHash];
            $where = ['user_id = ?' => (int)$customer['user_id']];
            $connection->update($customerEntityTable, $bind, $where);
        }
    }
}
