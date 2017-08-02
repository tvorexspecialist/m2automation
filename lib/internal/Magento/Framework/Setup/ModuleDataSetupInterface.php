<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * DB data resource interface for a module
 *
 * @api
 * @since 2.0.0
 */
interface ModuleDataSetupInterface extends SetupInterface
{
    const DEFAULT_SETUP_CONNECTION = 'default_setup';

    const VERSION_COMPARE_EQUAL = 0;

    const VERSION_COMPARE_LOWER = -1;

    const VERSION_COMPARE_GREATER = 1;

    const TYPE_DATA_INSTALL = 'data-install';

    const TYPE_DATA_UPGRADE = 'data-upgrade';

    /**
     * Retrieve row or field from table by id or string and parent id
     *
     * @param string $table
     * @param string $idField
     * @param string|integer $rowId
     * @param string|null $field
     * @param string|null $parentField
     * @param string|integer $parentId
     * @return mixed
     * @since 2.0.0
     */
    public function getTableRow($table, $idField, $rowId, $field = null, $parentField = null, $parentId = 0);

    /**
     * Delete table row
     *
     * @param string $table
     * @param string $idField
     * @param string|int $rowId
     * @param null|string $parentField
     * @param int|string $parentId
     * @return $this
     * @since 2.0.0
     */
    public function deleteTableRow($table, $idField, $rowId, $parentField = null, $parentId = 0);

    /**
     * Update one or more fields of table row
     *
     * @param string $table
     * @param string $idField
     * @param string|integer $rowId
     * @param string|array $field
     * @param mixed|null $value
     * @param string $parentField
     * @param string|integer $parentId
     * @return $this
     * @since 2.0.0
     */
    public function updateTableRow($table, $idField, $rowId, $field, $value = null, $parentField = null, $parentId = 0);

    /**
     * Gets event manager
     *
     * @return \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    public function getEventManager();

    /**
     * Gets filesystem
     *
     * @return \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    public function getFilesystem();

    /**
     * Create migration setup
     *
     * @param array $data
     * @return \Magento\Framework\Module\Setup\Migration
     * @since 2.0.0
     */
    public function createMigrationSetup(array $data = []);

    /**
     * Gets setup cache
     *
     * @return DataCacheInterface
     * @since 2.0.0
     */
    public function getSetupCache();
}
