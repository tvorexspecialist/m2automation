<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Event\ObserverInterface;

class ResetAttemptForFrontendObserver implements ObserverInterface
{
    /*
     * @var \Magento\Captcha\Model\Resource\LogFactory
     */
    public $resLogFactory;

    /**
     * @param \Magento\Captcha\Model\Resource\LogFactory $resLogFactory
     */
    public function __construct(
        \Magento\Captcha\Model\Resource\LogFactory $resLogFactory
    ) {
        $this->resLogFactory = $resLogFactory;
    }

    /**
     * Reset Attempts For Frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Captcha\Observer\ResetAttemptForFrontendObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Customer\Model\Customer $model */
        $model = $observer->getModel();

        return $this->resLogFactory->create()->deleteUserAttempts($model->getEmail());
    }
}
