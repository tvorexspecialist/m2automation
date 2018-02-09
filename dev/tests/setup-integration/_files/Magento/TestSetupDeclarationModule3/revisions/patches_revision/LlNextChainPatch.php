<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestSetupDeclarationModule3\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchRevertableInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * @package Magento\TestSetupDeclarationModule3\Setup
 */
class LlNextChainPatch implements
    DataPatchInterface,
    PatchRevertableInterface,
    PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * IncrementalSomeIntegerPatch constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '0.0.5';
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $adapter = $this->resourceConnection->getConnection();
        $adapter->insertArray('reference_table', ['for_patch_testing'], ['very_secret_string']);
    }

    public function revert()
    {
        $adapter = $this->resourceConnection->getConnection();
        $adapter->delete('test_table', ['varbinary = ?', 0101010]);
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [
            ZFirstPatch::class
        ];
    }
}
