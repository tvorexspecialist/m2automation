<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Event regex observer object
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Event\Observer;

/**
 * Class \Magento\Framework\Event\Observer\Regex
 *
 * @since 2.0.0
 */
class Regex extends \Magento\Framework\Event\Observer
{
    /**
     * Checkes the observer's event_regex against event's name
     *
     * @param \Magento\Framework\Event $event
     * @return boolean
     * @since 2.0.0
     */
    public function isValidFor(\Magento\Framework\Event $event)
    {
        return preg_match($this->getEventRegex(), $event->getName());
    }
}
