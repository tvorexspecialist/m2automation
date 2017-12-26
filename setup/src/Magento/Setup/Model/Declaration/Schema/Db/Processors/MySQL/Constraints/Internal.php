<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Constraints;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Detect primary or unique constraints and map them to appropriate format
 *
 * @inheritdoc
 */
class Internal implements DbSchemaProcessorInterface
{
    /**
     * Name of Primary Key
     */
    const PRIMARY_NAME = 'PRIMARY';

    /**
     * Primary key name, that is used in definition
     */
    const PRIMARY_KEY_NAME = 'PRIMARY KEY';

    /**
     * Uniqe key name, that is used in definition
     */
    const UNIQUE_KEY_NAME = 'UNIQUE KEY';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        $adapter = $this->resourceConnection->getConnection(
            $element->getTable()->getResource()
        );
        $columnsList = array_map(
            function (Column $column) use ($adapter) {
                return $adapter->quoteIdentifier($column->getName());
            },
            $element->getColumns()
        );

        return sprintf(
            '%s (%s)',
            $element->getType() === 'primary' ? 'PRIMARY KEY' : 'UNIQUE KEY',
            implode(',', $columnsList)
        );
    }

    /**
     * @inheritdoc
     */
    public function canBeApplied(ElementInterface $element)
    {
        return $element instanceof \Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal;
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        if (isset($data['Key_name'])) {
            $data = [
                'name' => $data['Key_name'],
                'column' => [
                    $data['Column_name'] => $data['Column_name']
                ],
                'type' => $data['Key_name'] === self::PRIMARY_NAME ? 'primary' : 'unique'
            ];
        }

        return $data;
    }
}
