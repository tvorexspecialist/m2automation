<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Cart;

/**
 * Class RequestInfoFilter used for filtering data from a request
 * @since 2.2.0
 */
class RequestInfoFilter implements RequestInfoFilterInterface
{
    /**
     * @var array $params
     * @since 2.2.0
     */
    private $filterList;

    /**
     * @param array $filterList
     * @since 2.2.0
     */
    public function __construct(
        array $filterList = []
    ) {
        $this->filterList = $filterList;
    }

    /**
     * Filters the data with values from filterList
     *
     * @param \Magento\Framework\DataObject $params
     * @return $this
     * @since 2.2.0
     */
    public function filter(\Magento\Framework\DataObject $params)
    {
        foreach ($this->filterList as $filterKey) {
            /** @var string $filterKey */
            if ($params->hasData($filterKey)) {
                $params->unsetData($filterKey);
            }
        }
        return $this;
    }
}
