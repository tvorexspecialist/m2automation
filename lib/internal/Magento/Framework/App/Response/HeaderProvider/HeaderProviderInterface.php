<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Response\HeaderProvider;

/**
 * Interface \Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface
 *
 */
interface HeaderProviderInterface
{
    /**
     * Whether the header should be attached to the response
     *
     * @return bool
     */
    public function canApply();

    /**
     * Header name
     *
     * @return string
     */
    public function getName();

    /**
     * Header value
     *
     * @return string
     */
    public function getValue();
}
