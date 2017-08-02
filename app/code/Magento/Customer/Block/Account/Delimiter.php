<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Account;

/**
 * Class for delimiter.
 *
 * @api
 * @since 2.2.0
 */
class Delimiter extends \Magento\Framework\View\Element\Template implements SortLinkInterface
{
    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
