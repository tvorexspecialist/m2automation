<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Schema;
use Magento\Setup\Model\Declaration\Schema\Dto\TableElementInterface;
use Magento\Setup\Model\Declaration\Schema\ElementHistoryFactory;
use Magento\Setup\Model\Declaration\Schema\Request;

/**
 * Holds information about all changes between 2 schemas: db and declaration XML
 * Holds 2 items:
 *  - new (Should be changed to)
 *  - old ()
 */
class Diff implements DiffInterface
{
    /**
     * @var array
     */
    private $changes;

    /**
     * This changes created only for debug reasons
     *
     * @var array
     */
    public $debugChanges;

    /**
     * @var array
     */
    private $whiteListTables = [];

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var ElementHistoryFactory
     */
    private $elementHistoryFactory;

    /**
     * @param ComponentRegistrar    $componentRegistrar
     * @param ElementHistoryFactory $elementHistoryFactory
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar,
        ElementHistoryFactory $elementHistoryFactory
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->elementHistoryFactory = $elementHistoryFactory;
    }

    /**
     * @inheritdoc
     */
    public function getAll()
    {
        return $this->changes;
    }

    /**
     * Retrieve array of whitelisted tables
     * Whitelist tables should have JSON format and should be added through
     * CLI command: should be done in next story
     *
     * @return array
     */
    private function getWhiteListTables()
    {
        if (!$this->whiteListTables) {
            foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $path) {
                $whiteListPath = $path . DIRECTORY_SEPARATOR . 'etc' .
                    DIRECTORY_SEPARATOR . 'declarative_schema_white_list.json';

                if (file_exists($whiteListPath)) {
                    $this->whiteListTables = array_replace_recursive(
                        $this->whiteListTables,
                        json_decode(file_get_contents($whiteListPath), true)
                    );
                }
            }
        }

        return $this->whiteListTables;
    }

    /**
     * Check whether element can be registered
     *
     * For example, if element is not in whitelist.json it cant
     * be registered due to backward incompatability
     *
     * @param  ElementInterface $object
     * @return bool
     */
    private function canBeRegistered(ElementInterface $object)
    {
        $whiteList = $this->getWhiteListTables();
        $type = $object->getType();

        if ($object instanceof TableElementInterface) {
            return isset($whiteList['table'][$object->getTable()->getName()][$type][$object->getName()]);
        }

        return isset($whiteList['table'][$object->getName()]);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function registerInstallationRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param TableElementInterface $dtoObject
     * @inheritdoc
     */
    public function register(ElementInterface $dtoObject, $operation, ElementInterface $oldDtoObject = null)
    {
        $historyData = ['new' => $dtoObject, 'old' => $oldDtoObject];
        $history = $this->elementHistoryFactory->create($historyData);
        $dtoObjectName = $dtoObject instanceof TableElementInterface ?
            $dtoObject->getTable()->getName() : $dtoObject->getName();
        //dtoObjects can have 4 types: column, constraint, index, table
        $this->changes[$dtoObjectName][$operation][] = $history;
        $this->debugChanges[$operation][] = $history;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function registerSchema(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Retrieve current schema
     * This function needs for rollback functionality
     *
     * @return Schema
     */
    public function getCurrentSchemaState()
    {
        return $this->schema;
    }

    /**
     * Request holds some information from cli command or UI
     * like: save mode or dry-run mode
     *
     * @return Request
     */
    public function getCurrentInstallationRequest()
    {
        return $this->request;
    }
}
