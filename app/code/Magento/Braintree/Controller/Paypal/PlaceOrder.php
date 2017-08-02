<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\Paypal;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Paypal\Helper;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class PlaceOrder
 * @since 2.1.0
 */
class PlaceOrder extends AbstractAction
{
    /**
     * @var Helper\OrderPlace
     * @since 2.1.0
     */
    private $orderPlace;

    /**
     * Logger for exception details
     *
     * @var LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param Session $checkoutSession
     * @param Helper\OrderPlace $orderPlace
     * @param LoggerInterface|null $logger
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        Helper\OrderPlace $orderPlace,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context, $config, $checkoutSession);
        $this->orderPlace = $orderPlace;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     * @since 2.1.0
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $agreement = array_keys($this->getRequest()->getPostValue('agreement', []));
        $quote = $this->checkoutSession->getQuote();

        try {
            $this->validateQuote($quote);

            $this->orderPlace->execute($quote, $agreement);

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            return $resultRedirect->setPath('checkout/onepage/success', ['_secure' => true]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        return $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
    }
}
