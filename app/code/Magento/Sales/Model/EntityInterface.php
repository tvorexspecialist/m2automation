<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model;

/**
 * Interface EntityInterface
 * @api
 * @since 100.0.2
 */
interface EntityInterface
{
    /**
     * @return string
     */
    public function getIncrementId();
}
