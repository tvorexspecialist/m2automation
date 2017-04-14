<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\DB;

use Magento\Framework\DB\Sql\ColumnValueExpression;

/**
 * Class ColumnsResolver
 *
 * Resolves columns names
 */
class ColumnsResolver
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * ColumnsResolver constructor.
     * @param NameResolver $nameResolver
     */
    public function __construct(
        NameResolver $nameResolver
    ) {
        $this->nameResolver = $nameResolver;
    }

    /**
     * Set columns list to SelectBuilder
     *
     * @param SelectBuilder $selectBuilder
     * @param array $entityConfig
     * @return array
     */
    public function getColumns(SelectBuilder $selectBuilder, $entityConfig)
    {
        if (!isset($entityConfig['attribute'])) {
            return [];
        }
        $group = [];
        $columns = $selectBuilder->getColumns();
        foreach ($entityConfig['attribute'] as $attributeData) {
            $columnAlias = $this->nameResolver->getAlias($attributeData);
            $tableAlias = $this->nameResolver->getAlias($entityConfig);
            $columnName = $this->nameResolver->getName($attributeData);
            if (isset($attributeData['function'])) {
                $prefix = '';
                if (isset($attributeData['distinct']) && $attributeData['distinct'] == true) {
                    $prefix = ' DISTINCT ';
                }
                $expression = new ColumnValueExpression(
                    strtoupper($attributeData['function']) . '(' . $prefix . $tableAlias . '.' . $columnName . ')'
                );
            } else {
                $expression = $tableAlias . '.' . $columnName;
            }
            $columns[$columnAlias] = $expression;
            if (isset($attributeData['group'])) {
                $group[$columnAlias] = $expression;
            }
        }
        $selectBuilder->setGroup(array_merge($selectBuilder->getGroup(), $group));
        return $columns;
    }
}
