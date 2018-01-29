<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\ColumnIdentityAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Find out whether column can be auto_incremented or not
 *
 * @inheritdoc
 */
class Identity implements DbDefinitionProcessorInterface
{
    /**
     * MySQL flag, that says that we need to increment field, each time when we add new row
     */
    const IDENTITY_FLAG = 'auto_increment';

    /**
     * @param ColumnIdentityAwareInterface $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        return $column->isIdentity() ? strtoupper(self::IDENTITY_FLAG) : '';
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        if (!empty($data['extra']) && stripos($data['extra'], self::IDENTITY_FLAG) !== false) {
            $data['identity'] = true;
        }

        return $data;
    }
}
