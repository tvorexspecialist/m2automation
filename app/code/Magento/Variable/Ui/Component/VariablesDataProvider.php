<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Ui\Component;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class VariablesDataProvider
 * @package Magento\Variable\Ui\Component
 */
class VariablesDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Magento\Variable\Model\VariableFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Email\Model\Source\Variables
     */
    private $storesVariables;

    /**
     * VariablesDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory $collectionFactory
     * @param \Magento\Email\Model\Source\Variables $storesVariables
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory $collectionFactory,
        \Magento\Email\Model\Source\Variables $storesVariables,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->storesVariables = $storesVariables;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Prepare default variables
     *
     * @return array
     */
    private function getDefaultVariables()
    {
        $variables = [];
        foreach ($this->storesVariables->getData() as $variable) {
            $variables[] = [
                'code' => $variable['value'],
                'variable_name' => $variable['label'],
                'variable_type' => \Magento\Email\Model\Source\Variables::DEFAULT_VARIABLE_TYPE
            ];
        }

        return $variables;
    }

    /**
     * Prepare custom variables
     *
     * @return array
     */
    private function getCustomVariables()
    {
        $customVariables = $this->collectionFactory->create();

        $variables = [];
        foreach ($customVariables->getData() as $variable) {
            $variables[] = [
                'code' => $variable['code'],
                'variable_name' => $variable['name'],
                'variable_type' => 'custom'
            ];
        }

        return $variables;
    }

    /**
     * Sort variables array by name.
     *
     * @param array $items
     * @param string $direction
     * @return array
     */
    private function sortByName($items, $direction)
    {
        usort($items, function ($item1, $item2) use ($direction) {
            return $this->variablesCompare($item1, $item2, 'variable_name', $direction);
        });
        return $items;
    }

    /**
     * Sort variables array by type.
     *
     * @param array $items
     * @param string $direction
     * @return array
     */
    private function sortByType($items, $direction)
    {
        usort($items, function ($item1, $item2) use ($direction) {
            return $this->variablesCompare($item1, $item2, 'variable_type', $direction);
        });
        return $items;
    }

    /**
     * Compare variables array's elements on index.
     *
     * @param array $variable1
     * @param array $variable2
     * @param string $partIndex
     * @param string $direction
     *
     * @return int
     */
    private function variablesCompare($variable1, $variable2, $partIndex, $direction)
    {
        $itemNames = [$variable1[$partIndex], $variable2[$partIndex]];
        sort($itemNames, SORT_STRING);
        $expectedIndex = $direction == SortOrder::SORT_ASC ? 0 : 1;
        return $variable1[$partIndex] === $itemNames[$expectedIndex] ? -1 : 1;
    }

    /**
     * Merge variables from different sources:
     * custom variables and default (stores configuration variables)
     *
     * @return array
     */
    public function getData()
    {
        $searchCriteria = $this->getSearchCriteria();
        $sortOrders = $searchCriteria->getSortOrders();

        // sort items by variable_type
        $sortOrder = $searchCriteria->getSortOrders();
        if (!empty($sortOrder) && $sortOrder[0]->getDirection() == 'DESC') {
            $items = array_merge(
                $this->getCustomVariables(),
                $this->getDefaultVariables()
            );
        } else {
            $items = array_merge(
                $this->getDefaultVariables(),
                $this->getCustomVariables()
            );
        }

        // filter array by variable_name and search value
        $filterGroups = $searchCriteria->getFilterGroups();
        if (!empty($filterGroups)) {
            $filters = $filterGroups[0]->getFilters();
            if (!empty($filters)) {
                $value = str_replace('%', '', $filters[0]->getValue());
                $items = array_values(array_filter($items, function ($item) use ($value) {
                    return strpos(strtolower($item['variable_name']), strtolower($value)) !== false;
                }));
            }
        }

        return [
            'items' => $items
        ];
    }
}
