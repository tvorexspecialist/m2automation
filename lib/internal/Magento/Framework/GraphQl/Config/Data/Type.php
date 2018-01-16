<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Data;

/**
 * Describes all the configured data of an Output or Input type in GraphQL.
 */
class Type implements StructureInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var array
     */
    private $interfaces;

    /**
     * @var string
     */
    private $description;

    /**
     * @param string $name
     * @param array $fields
     * @param array $interfaces
     * @param string $description
     */
    public function __construct(
        string $name,
        array $fields,
        array $interfaces,
        string $description
    ) {
        $this->name = $name;
        $this->fields = $fields;
        $this->interfaces = $interfaces;
        $this->description = $description;
    }

    /**
     * Get the type name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get a list of fields that make up the possible return or input values of a type.
     *
     * @return Field[]
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * Get interfaces the type implements, if any. Return an empty array if none are configured.
     *
     * @return array
     */
    public function getInterfaces() : array
    {
        return $this->interfaces;
    }

    /**
     * Get a human-readable description of the type.
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }
}
