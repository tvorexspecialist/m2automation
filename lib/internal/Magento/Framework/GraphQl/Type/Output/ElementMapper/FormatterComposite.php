<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Type\Output\ElementMapper;

use Magento\Framework\GraphQl\Type\Definition\OutputType;
use Magento\Framework\GraphQl\Config\Data\TypeInterface;

/**
 * {@inheritdoc}
 */
class FormatterComposite implements FormatterInterface
{
    /**
     * @var FormatterInterface[]
     */
    private $formatters;

    /**
     * @param FormatterInterface[] $formatters
     */
    public function __construct(array $formatters)
    {
        $this->formatters = $formatters;
    }

    /**
     * {@inheritDoc}
     */
    public function format(TypeInterface $typeStructure, OutputType $outputType) : array
    {
        $config = [
            'name' => $typeStructure->getName(),
            'description' => $typeStructure->getDescription()
        ];
        foreach ($this->formatters as $formatter) {
            $config = array_merge($config, $formatter->format($typeStructure, $outputType));
        }

        return $config;
    }
}
