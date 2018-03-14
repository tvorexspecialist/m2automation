<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Type\Output\ElementMapper\Formatter;

use Magento\Framework\GraphQl\Type\Definition\OutputType;
use Magento\Framework\GraphQl\Config\Data\TypeInterface;
use Magento\Framework\GraphQl\Type\Output\ElementMapper\FormatterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Config\Data\InterfaceType;

/**
 * Add resolveType field to schema config array based on type structure properties.
 */
class ResolveType implements FormatterInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritDoc}
     */
    public function format(TypeInterface $typeStructure, OutputType $outputType) : array
    {
        $config = [];
        if ($typeStructure instanceof InterfaceType) {
            $typeResolver = $this->objectManager->create($typeStructure->getTypeResolver());
            $config['resolveType'] = function ($value) use ($typeResolver) {
                return $typeResolver->resolveType($value);
            };
        }

        return $config;
    }
}
