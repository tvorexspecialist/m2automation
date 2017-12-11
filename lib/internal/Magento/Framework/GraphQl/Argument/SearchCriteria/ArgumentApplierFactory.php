<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\SearchCriteria;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for the creation of @see ArgumentApplierInterface classes used in search criteria
 */
class ArgumentApplierFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create a search criteria argument applier instance
     *
     * @param string $argumentName
     * @return ArgumentApplierInterface
     * @throws \LogicException
     */
    public function create(string $argumentName)
    {
        $appliers = [
            'filter' => ArgumentApplier\Filter::class,
            'pageSize' => ArgumentApplier\PageSize::class,
            'currentPage' => ArgumentApplier\CurrentPage::class,
            'sort' => ArgumentApplier\Sort::class,
            'search' => ArgumentApplier\Search::class
        ];
        if (isset($appliers[$argumentName])) {
            return $this->objectManager->create($appliers[$argumentName]);
        } else {
            throw new \LogicException(sprintf('Applier %s not found', $argumentName));
        }
    }
}
