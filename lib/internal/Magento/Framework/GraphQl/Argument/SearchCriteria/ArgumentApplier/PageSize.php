<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Argument\SearchCriteria\ArgumentApplier;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Argument\SearchCriteria\ArgumentApplierInterface;
use Magento\Framework\Phrase;

/**
 * Class for PageSize Argument
 */
class PageSize implements ArgumentApplierInterface
{
    const ARGUMENT_NAME = 'pageSize';

    /**
     * {@inheritdoc}
     */
    public function applyArgument(SearchCriteriaInterface $searchCriteria, $argument) : SearchCriteriaInterface
    {
        if (is_int($argument) || is_string($argument)) {
            $searchCriteria->setPageSize($argument);
        } else {
            throw new \Magento\Framework\Exception\RuntimeException(
                new Phrase('Argument %1 not of type Int or String', [$argument])
            );
        }
        return $searchCriteria;
    }
}
