<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\Eav\Api\AttributeManagementInterface;
use Magento\GraphQl\Model\Type\Helper\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Define ProductAttributeSearchCriteria's GraphQL type
 */
class ProductAttributeSearchCriteria implements HandlerInterface
{
    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @var AttributeManagementInterface
     */
    private $management;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param TypeGenerator $typeGenerator
     * @param AttributeManagementInterface $management
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        TypeGenerator $typeGenerator,
        AttributeManagementInterface $management,
        TypeFactory $typeFactory
    ) {
        $this->typeGenerator = $typeGenerator;
        $this->management = $management;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return $this->typeFactory->createInputObject(
            [
                'name' => $reflector->getShortName(),
                'fields' => $this->getFields()
            ]
        );
    }

    /**
     * Retrieve fields
     *
     * @return \Closure|array
     */
    private function getFields()
    {
        $reflector = new \ReflectionClass($this);
        $className = $reflector->getShortName();
        $schema = [];
        $attributes = $this->management->getAttributes('catalog_product', 4);
        foreach ($attributes as $attribute) {
            $schema[$attribute->getAttributeCode()] = 'SearchCriteriaExpression';
        }

        $schema = array_merge(
            $schema,
            ['or' => $className]
        );
        $resolvedTypes = $this->typeGenerator->generate($className, $schema);
        $fields = $resolvedTypes->config['fields'];

        return $fields;
    }
}
