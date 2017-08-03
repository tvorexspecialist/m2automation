<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Persistent\Observer\SetQuotePersistentDataObserver
 *
 * @since 2.0.0
 */
class SetQuotePersistentDataObserver implements ObserverInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     * @since 2.0.0
     */
    protected $_persistentSession = null;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.0.0
     */
    protected $_persistentData = null;

    /**
     * @var \Magento\Persistent\Model\QuoteManager
     * @since 2.0.0
     */
    protected $quoteManager;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Persistent\Model\QuoteManager $quoteManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Persistent\Model\QuoteManager $quoteManager,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_persistentSession = $persistentSession;
        $this->quoteManager = $quoteManager;
        $this->_customerSession = $customerSession;
        $this->_persistentData = $persistentData;
    }

    /**
     * Set persistent data into quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_persistentSession->isPersistent()) {
            return;
        }

        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        if ((
                ($this->_persistentSession->isPersistent() && !$this->_customerSession->isLoggedIn())
                && !$this->_persistentData->isShoppingCartPersist()
            )
            && $this->quoteManager->isPersistent()
        ) {
            //Quote is not actual customer's quote, just persistent
            $quote->setIsPersistent(true);
        }
    }
}
