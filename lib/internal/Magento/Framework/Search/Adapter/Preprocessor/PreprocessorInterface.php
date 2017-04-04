<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Preprocessor;

interface PreprocessorInterface
{
    /**
     * @param string $query
     * @return string
     */
    public function process($query);
}
