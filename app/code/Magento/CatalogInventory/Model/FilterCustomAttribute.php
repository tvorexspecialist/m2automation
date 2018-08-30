<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

class FilterCustomAttribute
{
    /**
     * @var array
     */
    private $blackList;

    /**
     * @param array $blackList
     */
    public function __construct(array $blackList = [])
    {
        $this->blackList = $blackList;
    }

    /**
     * Delete custom attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param array $attributes
     * @return mixed
     */
    public function execute(array $attributes)
    {
        return array_diff($attributes, $this->blackList);
    }
}
