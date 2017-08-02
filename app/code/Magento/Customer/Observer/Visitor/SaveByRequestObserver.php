<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer\Visitor;

use Magento\Framework\Event\Observer;

/**
 * Visitor Observer
 * @since 2.0.0
 */
class SaveByRequestObserver extends AbstractVisitorObserver
{
    /**
     * saveByRequest
     *
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        $this->visitor->saveByRequest($observer);
    }
}
