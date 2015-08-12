<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\TestCase;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Page\PageFactory;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Precondition:
 * 1. Create items
 *
 * Steps:
 * 1. Navigate to backend.
 * 2. Go to grid page
 * 3. Filter grid using provided columns
 * 5. Perform Asserts
 *
 * @group Ui_(CS)
 * @ZephyrId MAGETWO-41329
 */
class GridFilteringTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Order index page.
     *
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Injection data.
     *
     * @param PageFactory $pageFactory
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(PageFactory $pageFactory, FixtureFactory $fixtureFactory)
    {
        $this->pageFactory = $pageFactory;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * @param string $fixtureName
     * @param string $fixtureDataSet
     * @param int $itemsCount
     * @param string $steps
     * @param string $pageClass
     * @param string $gridRetriever
     * @param array $filters
     * @return array
     */
    public function test(
        $pageClass,
        $gridRetriever,
        $filters,
        $fixtureName,
        $fixtureDataSet,
        $itemsCount,
        $steps
    ) {
        $items = $this->createItems($itemsCount, $fixtureName, $fixtureDataSet, $steps);
        $page = $this->pageFactory->create($pageClass);

        // Steps
        $page->open();
        /** @var DataGrid $gridBlock */
        $gridBlock = $page->$gridRetriever();
        $gridBlock->resetFilter();

        //$filtersArray = $this->getFiltersArray($filters);
        $filterResults = [];
        foreach ($filters as $index => $itemFilters) {
            foreach ($itemFilters as $itemFiltersName => $itemFilterValue) {
                $gridBlock->search(
                    [$itemFiltersName => $itemFilterValue ?: $items[$index]->getData($itemFiltersName)]
                );
                $filteredIds = $this->getActualIds($gridBlock->getAllIds(), $items);
                $filterResults[$items[$index]->getId()][$itemFiltersName] = $filteredIds;
            }
        }

        return ['filterResults' => $filterResults];
    }

    /**
     * @param string $filtersString
     * @return array
     */
    protected function getFiltersArray($filtersString)
    {
        $itemsFilters = explode('|', $filtersString);

        $filters = [];
        foreach ($itemsFilters as $itemFiltersString) {
            $itemFilters = explode(',', $itemFiltersString);
            $resultItemFilters = [];
            foreach ($itemFilters as $itemFilterString) {
                $itemFilter = explode('=', $itemFilterString);
                $resultItemFilters[$itemFilter[0]] = isset($itemFilter[1]) ? $itemFilter[1] : null;
            }
            $filters[] = $resultItemFilters;
        }

        return $filters;
    }

    /**
     * @param string[] $ids
     * @param FixtureInterface[] $items
     * @return string[]
     */
    protected function getActualIds(array $ids, array $items)
    {
        $actualIds = [];
        foreach ($items as $item) {
            if (in_array($item->getId(), $ids)) {
                $actualIds[] = $item->getId();
            }
        }
        return  $actualIds;
    }

    /**
     * @param int $itemsCount
     * @param string $fixtureName
     * @param string $fixtureDataSet
     * @param string $steps
     * @return FixtureInterface[]
     */
    protected function createItems($itemsCount, $fixtureName, $fixtureDataSet, $steps)
    {
        $items = [];
        $steps = explode('|', $steps);
        for ($i = 0; $i < $itemsCount; $i++) {
            $item = $this->fixtureFactory->createByCode($fixtureName, ['dataset' => $fixtureDataSet]);
            $item->persist();
            $items[$i] = $item;
            $this->processSteps($item, $steps[$i]);
        }

        return $items;
    }

    /**
     * @param FixtureInterface $item
     * @param string $steps
     */
    protected function processSteps(FixtureInterface $item, $steps)
    {
        $steps = array_diff(explode(',', $steps), ['-']);
        foreach ($steps as $step) {
            $processStep = $this->objectManager->create($step, ['order' => $item]);
            $processStep->run();
        }
    }
}
