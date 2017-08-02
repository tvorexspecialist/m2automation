<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

/**
 * Interface ReportingInterface
 * @since 2.0.0
 */
interface ReportingInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     * @since 2.0.0
     */
    public function search(SearchCriteriaInterface $searchCriteria);
}
