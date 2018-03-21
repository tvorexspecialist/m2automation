<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\GraphQlReader\MetaReader;

class FieldMetaReader
{
    /**
     * @var TypeMetaReader
     */
    private $typeMetaReader;

    /**
     * @var DocReader
     */
    private $docReader;

    /**
     * @param TypeMetaReader $typeMetaReader
     * @param DocReader $docReader
     */
    public function __construct(TypeMetaReader $typeMetaReader, DocReader $docReader)
    {
        $this->typeMetaReader = $typeMetaReader;
        $this->docReader = $docReader;
    }

    /**
     * @param \GraphQL\Type\Definition\FieldDefinition $fieldMeta
     * @return array
     */
    public function readFieldMeta(\GraphQL\Type\Definition\FieldDefinition $fieldMeta) : array
    {
        $fieldName = $fieldMeta->name;
        $fieldTypeMeta = $fieldMeta->getType();
        $result = [
            'name' => $fieldName,
            'arguments' => []
        ];

        $fieldResolver = $this->readFieldResolver($fieldMeta);
        if ($fieldResolver) {
            $result['resolver'] = $fieldResolver;
        }

        $result = array_merge(
            $result,
            $this->typeMetaReader->readTypeMeta($fieldTypeMeta, 'OutputField')
        );

        if ($this->docReader->readTypeDescription($fieldMeta->astNode->directives)) {
                $result['description'] = $this->docReader->readTypeDescription($fieldMeta->astNode->directives);
        }

        $arguments = $fieldMeta->args;
        foreach ($arguments as $argumentMeta) {
            $argumentName = $argumentMeta->name;
            $result['arguments'][$argumentName] = [
                'name' => $argumentName,
            ];
            $typeMeta = $argumentMeta->getType();
            $result['arguments'][$argumentName] = array_merge(
                $result['arguments'][$argumentName],
                $this->typeMetaReader->readTypeMeta($typeMeta, 'Argument')
            );

            if ($this->docReader->readTypeDescription($argumentMeta->astNode->directives)) {
                $result['arguments'][$argumentName]['description'] =
                    $this->docReader->readTypeDescription($argumentMeta->astNode->directives);
            }
        }
        return $result;
    }

    /**
     * @param \GraphQL\Type\Definition\FieldDefinition $fieldMeta
     * @return string|null
     */
    private function readFieldResolver(\GraphQL\Type\Definition\FieldDefinition $fieldMeta) : ?string
    {
        /** @var \GraphQL\Language\AST\NodeList $directives */
        $directives = $fieldMeta->astNode->directives;
        foreach ($directives as $directive) {
            if ($directive->name->value == 'resolver') {
                foreach ($directive->arguments as $directiveArgument) {
                    if ($directiveArgument->name->value == 'class') {
                        return $directiveArgument->value->value;
                    }
                }
            }
        }
        return null;
    }
}
