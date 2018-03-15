<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config;

class GraphQlReader implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * File locator
     *
     * @var \Magento\Framework\Config\FileResolverInterface
     */
    protected $fileResolver;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    protected $defaultScope;

    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        $fileName = 'schema.graphql',
        $defaultScope = 'global'
    ) {
        $this->fileResolver = $fileResolver;
        $this->defaultScope = $defaultScope;
        $this->fileName = $fileName;
    }

    public function read($scope = null) : array
    {
        $result = [];
        $scope = $scope ?: $this->defaultScope;
        $schemaFiles = $this->fileResolver->get($this->fileName, $scope);
        if (!count($schemaFiles)) {
            return $result;
        }

        /**
         * Compatible with @see \Magento\Framework\GraphQl\Config\GraphQlReader::parseTypes
         */
        $knownTypes = [];
        foreach ($schemaFiles as $partialSchemaContent) {
            $partialSchemaTypes = $this->parseTypes($partialSchemaContent);
            /**
             * Keep declarations from current partial schema, add missing declarations from all previously read schemas
             */
            $knownTypes = $partialSchemaTypes + $knownTypes;
            $schemaContent = implode("\n", $knownTypes);
            $partialResult = [];
            $schema = \GraphQL\Utils\BuildSchema::build($schemaContent);
            $typeMap = $schema->getTypeMap();
            foreach ($typeMap as $typeName => $typeMeta) {
                if (strpos($typeName, '__') === 0) {
                    // Skip built-in object types
                    continue;
                }

                if ($typeMeta instanceof \GraphQL\Type\Definition\ScalarType) {
                    // Skip built-in scalar types
                    continue;
                }

                // TODO: Use polymorphism instead
                if ($typeMeta instanceof \GraphQL\Type\Definition\EnumType) {
                    $partialResult[$typeName] = $this->readEnumTypeMeta($typeMeta);
                    continue;
                }
                if ($typeMeta instanceof \GraphQL\Type\Definition\ObjectType) {
                    $partialResult[$typeName] = $this->readObjectTypeMeta($typeMeta);
                    continue;
                }
                if ($typeMeta instanceof \GraphQL\Type\Definition\InputObjectType) {
                    $partialResult[$typeName] = $this->readInputObjectTypeMeta($typeMeta);
                    continue;
                }
                if ($typeMeta instanceof \GraphQL\Type\Definition\InterfaceType) {
                    $partialResult[$typeName] = $this->readInterfaceTypeMeta($typeMeta);
                    continue;
                }
                // TODO: This is necessary to catch unprocessed GraphQL types, like unions if the will be used in schema
                throw new \LogicException("'{$typeName}' cannot be processed.");
            }
            $result = array_replace_recursive($result, $partialResult);
        }

        return $result;
    }


    /**
     * @param \GraphQL\Type\Definition\EnumType $typeMeta
     * @return array
     */
    private function readEnumTypeMeta(\GraphQL\Type\Definition\EnumType $typeMeta) : array
    {
        $result = [
            'name' => $typeMeta->name,
            'type' => 'graphql_enum',
            'items' => [] // Populated later
        ];
        foreach ($typeMeta->getValues() as $value) {
            // TODO: Simplify structure, currently name is lost during conversion to GraphQL schema
            $result['items'][$value->value] = [
                'name' => strtolower($value->name),
                '_value' => $value->value
            ];
        }

        return $result;
    }

    /**
     * @param string $type
     * @return bool
     */
    private function isScalarType(string $type) : bool
    {
        return in_array($type, ['String', 'Int', 'Float', 'Boolean', 'ID']);
    }

    /**
     * @param \GraphQL\Type\Definition\ObjectType $typeMeta
     * @return array
     */
    private function readObjectTypeMeta(\GraphQL\Type\Definition\ObjectType $typeMeta) : array
    {
        $typeName = $typeMeta->name;
        $result = [
            'name' => $typeName,
            'type' => 'graphql_type',
            'fields' => [], // Populated later

        ];

        $interfaces = $typeMeta->getInterfaces();
        foreach ($interfaces as $interfaceMeta) {
            $interfaceName = $interfaceMeta->name;
            $result['implements'][$interfaceName] = [
                'interface' => $interfaceName,
                'copyFields' => true // TODO: Configure in separate config
            ];
        }

        $fields = $typeMeta->getFields();
        foreach ($fields as $fieldName => $fieldMeta) {
            $result['fields'][$fieldName] = $this->readFieldMeta($fieldMeta);
        }

        return $result;
    }

    /**
     * @param \GraphQL\Type\Definition\InputObjectType $typeMeta
     * @return array
     */
    private function readInputObjectTypeMeta(\GraphQL\Type\Definition\InputObjectType $typeMeta) : array
    {
        $typeName = $typeMeta->name;
        $result = [
            'name' => $typeName,
            'type' => 'graphql_input',
            'fields' => [] // Populated later
        ];
        $fields = $typeMeta->getFields();
        foreach ($fields as $fieldName => $fieldMeta) {
            $result['fields'][$fieldName] = $this->readInputObjectFieldMeta($fieldMeta);
        }
        return $result;
    }

    /**
     * @param \GraphQL\Type\Definition\InterfaceType $typeMeta
     * @return array
     */
    private function readInterfaceTypeMeta(\GraphQL\Type\Definition\InterfaceType $typeMeta) : array
    {
        $typeName = $typeMeta->name;
        $result = [
            'name' => $typeName,
            'type' => 'graphql_interface',
            'fields' => []
        ];

        $interfaceTypeResolver = $this->readInterfaceTypeResolver($typeMeta);
        if ($interfaceTypeResolver) {
            $result['typeResolver'] = $interfaceTypeResolver;
        }

        $fields = $typeMeta->getFields();
        foreach ($fields as $fieldName => $fieldMeta) {
            $result['fields'][$fieldName] = $this->readFieldMeta($fieldMeta);
        }
        return $result;
    }

    /**
     * @param \GraphQL\Type\Definition\FieldDefinition $fieldMeta
     * @return array
     */
    private function readFieldMeta(\GraphQL\Type\Definition\FieldDefinition $fieldMeta) : array
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
            $this->readTypeMeta($fieldTypeMeta, 'OutputField')
        );

        $arguments = $fieldMeta->args;
        foreach ($arguments as $argumentMeta) {
            $argumentName = $argumentMeta->name;
            $result['arguments'][$argumentName] = [
                'name' => $argumentName,
            ];
            $typeMeta = $argumentMeta->getType();
            $result['arguments'][$argumentName] = array_merge(
                $result['arguments'][$argumentName],
                $this->readTypeMeta($typeMeta, 'Argument')
            );
        }
        return $result;
    }

    /**
     * @param \GraphQL\Type\Definition\InputObjectField $fieldMeta
     * @return array
     */
    private function readInputObjectFieldMeta(\GraphQL\Type\Definition\InputObjectField $fieldMeta) : array
    {
        $fieldName = $fieldMeta->name;
        $typeMeta = $fieldMeta->getType();
        $result = [
            'name' => $fieldName,
            'required' => false,
            // TODO arguments don't make sense here, but expected to be always present in \Magento\Framework\GraphQl\Config\Data\Mapper\TypeMapper::map
            'arguments' => []
        ];

        $result = array_merge($result, $this->readTypeMeta($typeMeta, 'InputField'));
        return $result;
    }

    /**
     * @param $meta
     * @param string $parameterType Argument|OutputField|InputField
     * @return array
     */
    private function readTypeMeta($meta, $parameterType = 'Argument') : array
    {
        $result = [];
        if ($meta instanceof \GraphQL\Type\Definition\NonNull) {
            $result['required'] = true;
            $meta = $meta->getWrappedType();
        } else {
            $result['required'] = false;
        }
        if ($meta instanceof \GraphQL\Type\Definition\ListOfType) {
            $itemTypeMeta = $meta->ofType;
            if ($itemTypeMeta instanceof \GraphQL\Type\Definition\NonNull) {
                $result['itemsRequired'] = true;
                $itemTypeMeta = $itemTypeMeta->getWrappedType();
            } else {
                $result['itemsRequired'] = false;
            }
            $result['description'] = $itemTypeMeta->description;
            $itemTypeName = $itemTypeMeta->name;
            $result['itemType'] = $itemTypeName;
            if ($this->isScalarType((string)$itemTypeMeta)) {
                $result['type'] = 'ScalarArray' . $parameterType;
            } else {
                $result['type'] = 'ObjectArray' . $parameterType;
            }
        } else {
            $result['description'] = $meta->description;
            $result['type'] = $meta->name;
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

    /**
     * @param \GraphQL\Type\Definition\InterfaceType $interfaceTypeMeta
     * @return string|null
     */
    private function readInterfaceTypeResolver(\GraphQL\Type\Definition\InterfaceType $interfaceTypeMeta) : ?string
    {
        /** @var \GraphQL\Language\AST\NodeList $directives */
        $directives = $interfaceTypeMeta->astNode->directives;
        foreach ($directives as $directive) {
            if ($directive->name->value == 'typeResolver') {
                foreach ($directive->arguments as $directiveArgument) {
                    if ($directiveArgument->name->value == 'class') {
                        return $directiveArgument->value->value;
                    }
                }
            }
        }
        return null;
    }

    /**
     * @param string $graphQlSchemaContent
     * @return array [$typeName => $typeDeclaration, ...]
     */
    private function parseTypes($graphQlSchemaContent) : array
    {
        $typeKindsPattern = '(type|interface|union|enum|input)';
        $typeNamePattern = '[_A-Za-z][_0-9A-Za-z]*';
        $typeDefinitionPattern = '.*\{[^\}]*\}';
        $spacePattern = '[\s\t\n\r]*';
        preg_match_all(
            "/{$typeKindsPattern}{$spacePattern}({$typeNamePattern}){$spacePattern}{$typeDefinitionPattern}/i",
            $graphQlSchemaContent,
            $matches
        );
        /**
         * $matches[0] is an indexed array with the whole type definitions
         * $matches[2] is an indexed array with type names
         */
        $parsedTypes = array_combine($matches[2], $matches[0]);
        return $parsedTypes;
    }
}
