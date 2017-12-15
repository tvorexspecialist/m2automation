<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Process decimal type and separate it into type, scale and precission
 *
 * @inheritdoc
 */
class Decimal implements DbSchemaProcessorInterface
{
    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * @var Unsigned
     */
    private $unsigned;

    /**
     * @param Nullable $nullable
     * @param Unsigned $unsigned
     */
    public function __construct(Nullable $nullable, Unsigned $unsigned)
    {
        $this->nullable = $nullable;
        $this->unsigned = $unsigned;
    }

    /**
     * @inheritdoc
     */
    public function canBeApplied(ElementInterface $element)
    {
        return $element instanceof \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Decimal;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Decimal $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return sprintf(
            '%s(%s, %s) %s %s %s',
            $element->getElementType(),
            $element->getScale(),
            $element->getPrecission(),
            $this->unsigned->toDefinition($element),
            $this->nullable->toDefinition($element),
            $element->getDefault()
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match('/^(float|decimal|double)\((\d+),(\d+)\)/', $data['type'], $matches)) {
            /**
             * match[1] - type
             * match[2] - precision
             * match[3] - scale
             */
            $data['type'] = $matches[1];

            if (isset($matches[2]) && isset($matches[3])) {
                $data['scale'] = $matches[3];
                $data['precission'] = $matches[2];
            }
        }

        return $data;
    }
}
