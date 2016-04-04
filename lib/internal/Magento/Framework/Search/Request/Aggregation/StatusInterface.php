<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

interface StatusInterface
{
    /**
     * @return bool
     */
    public function isEnabled();
}
