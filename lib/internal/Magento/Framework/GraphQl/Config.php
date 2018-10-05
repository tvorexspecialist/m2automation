<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\GraphQl\Config\ConfigElementFactoryInterface;
use Magento\Framework\GraphQl\Config\ConfigElementInterface;
use Magento\Framework\GraphQl\Query\Fields as QueryFields;

/**
 * Provides access to typing information for a configured GraphQL schema.
 */
class Config implements ConfigInterface
{
    /**
     * @var DataInterface
     */
    private $configData;

    /**
     * @var ConfigElementFactoryInterface
     */
    private $configElementFactory;

    /**
     * @var QueryFields
     */
    private $queryFields;

    /**
     * @param DataInterface $data
     * @param ConfigElementFactoryInterface $configElementFactory
     * @param QueryFields $queryFields
     */
    public function __construct(
        DataInterface $data,
        ConfigElementFactoryInterface $configElementFactory,
        QueryFields $queryFields
    ) {
        $this->configData = $data;
        $this->configElementFactory = $configElementFactory;
        $this->queryFields = $queryFields;
    }

    /**
     * Get a data object with data pertaining to a GraphQL type's structural makeup.
     *
     * @param string $configElementName
     * @return ConfigElementInterface
     * @throws \LogicException
     */
    public function getConfigElement(string $configElementName) : ConfigElementInterface
    {
        $data = $this->configData->get($configElementName);
        if (!isset($data['type'])) {
            throw new \LogicException(
                sprintf('Config element "%s" is not declared in GraphQL schema', $configElementName)
            );
        }

        $fieldsInQuery = $this->queryFields->getFieldsUsedInQuery();
        if (isset($data['fields']) && !empty($fieldsInQuery)) {
           foreach ($data['fields'] as $fieldName => $fieldConfig) {
               if (!isset($fieldsInQuery[$fieldName])) {
                   unset($data['fields'][$fieldName]);
               }
           }
        }

        return $this->configElementFactory->createFromConfigData($data);
    }

    /**
     * Return all type names declared in a GraphQL schema's configuration.
     *
     * @return string[]
     */
    public function getDeclaredTypeNames() : array
    {
        $types = [];
        foreach ($this->configData->get(null) as $item) {
            if (isset($item['type']) && $item['type'] == 'graphql_type') {
                $types[] = $item['name'];
            }
        }
        return $types;
    }
}
