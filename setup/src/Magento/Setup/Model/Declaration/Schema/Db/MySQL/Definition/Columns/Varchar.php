<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Process 3 different types: char, varchar, varbinary
 *
 * @inheritdoc
 */
class Varchar implements DbDefinitionProcessorInterface
{
    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Comment
     */
    private $comment;

    /**
     * @param Nullable $nullable
     * @param ResourceConnection $resourceConnection
     * @param Comment $comment
     */
    public function __construct(Nullable $nullable, ResourceConnection $resourceConnection, Comment $comment)
    {
        $this->nullable = $nullable;
        $this->resourceConnection = $resourceConnection;
        $this->comment = $comment;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Varchar   $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        if ($column->getDefault() !== null) {
            $default = sprintf('DEFAULT "%s"', $column->getDefault());
        } else {
            $default = '';
        }

        return sprintf(
            '%s %s(%s) %s %s %s',
            $this->resourceConnection->getConnection()->quoteIdentifier($column->getName()),
            $column->getType(),
            $column->getLength(),
            $this->nullable->toDefinition($column),
            $default,
            $this->comment->toDefinition($column)
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match('/^(char|varchar|varbinary)\((\d+)\)/', $data['definition'], $matches)) {
            $data['length'] = $matches[2];
        }

        return $data;
    }
}
