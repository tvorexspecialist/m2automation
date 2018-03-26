<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Type\Output;

use Magento\Framework\GraphQl\Config\ConfigInterface;
use Magento\Framework\GraphQl\Type\Definition\OutputType;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Phrase;

/**
 * Map type names to their output type/interface classes.
 */
class OutputMapper
{
    /**
     * @var OutputFactory
     */
    private $outputFactory;

    /**
     * @var OutputType[]
     */
    private $outputTypes;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param OutputFactory $outputFactory
     * @param TypeFactory $typeFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        OutputFactory $outputFactory,
        TypeFactory $typeFactory,
        ConfigInterface $config
    ) {
        $this->outputFactory = $outputFactory;
        $this->config = $config;
        $this->typeFactory = $typeFactory;
    }

    /**
     * Get GraphQL output type object by type name.
     *
     * @param string $typeName
     * @return OutputType
     * @throws GraphQlInputException
     */
    public function getOutputType($typeName)
    {
        if (!isset($this->outputTypes[$typeName])) {
            $configElement = $this->config->getTypeStructure($typeName);
            $this->outputTypes[$typeName] = $this->outputFactory->create($configElement);
            if (!($this->outputTypes[$typeName] instanceof OutputType)) {
                throw new GraphQlInputException(
                    new Phrase("Type '{$typeName}' was requested but is not declared in the GraphQL schema.")
                );
            }
        }

        return $this->outputTypes[$typeName];
    }
}
