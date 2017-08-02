<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Reports Event observer model
 * @since 2.0.0
 */
class WishlistShareObserver implements ObserverInterface
{
    /**
     * @var EventSaver
     * @since 2.0.0
     */
    protected $eventSaver;

    /**
     * @param EventSaver $eventSaver
     * @since 2.0.0
     */
    public function __construct(
        EventSaver $eventSaver
    ) {
        $this->eventSaver = $eventSaver;
    }

    /**
     * Share customer wishlist action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->eventSaver->save(
            \Magento\Reports\Model\Event::EVENT_WISHLIST_SHARE,
            $observer->getEvent()->getWishlist()->getId()
        );
    }
}
