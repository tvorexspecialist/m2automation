<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Type\Output;

use Magento\Framework\GraphQl\Config\Data\StructureInterface;
use Magento\Framework\GraphQl\Type\Output\ElementMapper\FormatterInterface;
use Magento\Framework\GraphQl\Type\Definition\OutputType;

/**
 * Takes types represented with structure objects and maps them to GraphQL type-readable array formats
 */
class ElementMapper
{
    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @param FormatterInterface $formatter
     */
    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Build GraphQL formatted schema configuration using defined type structures.
     *
     * @param StructureInterface $structure
     * @param OutputType $outputType
     * @return array
     */
    public function buildSchemaArray(StructureInterface $structure, OutputType $outputType) : array
    {
        return $this->formatter->format($structure, $outputType);
    }
}
