<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model;

/**
 * Interface EntityInterface
 * @api
 * @since 2.0.0
 */
interface EntityInterface
{
    /**
     * @return string
     * @since 2.0.0
     */
    public function getIncrementId();
}
