<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Transparent;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\Session\SessionManager;
use Magento\Paypal\Model\Payflow\Service\Request\SecureToken;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Quote\Model\Quote;

/**
 * Class RequestSecureToken
 *
 * @package Magento\Paypal\Controller\Transparent
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class RequestSecureToken extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JsonFactory
     * @since 2.0.0
     */
    protected $resultJsonFactory;

    /**
     * @var Generic
     * @since 2.0.0
     */
    private $sessionTransparent;

    /**
     * @var SecureToken
     * @since 2.0.0
     */
    private $secureTokenService;

    /**
     * @var SessionManager
     * @since 2.0.0
     */
    private $sessionManager;

    /**
     * @var Transparent
     * @since 2.0.0
     */
    private $transparent;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Generic $sessionTransparent
     * @param SecureToken $secureTokenService
     * @param SessionManager $sessionManager
     * @param Transparent $transparent
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Generic $sessionTransparent,
        SecureToken $secureTokenService,
        SessionManager $sessionManager,
        Transparent $transparent
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sessionTransparent = $sessionTransparent;
        $this->secureTokenService = $secureTokenService;
        $this->sessionManager = $sessionManager;
        $this->transparent = $transparent;
        parent::__construct($context);
    }

    /**
     * Send request to PayfloPro gateway for get Secure Token
     *
     * @return ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var Quote $quote */
        $quote = $this->sessionManager->getQuote();

        if (!$quote or !$quote instanceof Quote) {
            return $this->getErrorResponse();
        }

        $this->sessionTransparent->setQuoteId($quote->getId());
        try {
            $token = $this->secureTokenService->requestToken($quote);
            if (!$token->getData('securetoken')) {
                throw new \LogicException();
            }

            return $this->resultJsonFactory->create()->setData(
                [
                    $this->transparent->getCode() => ['fields' => $token->getData()],
                    'success' => true,
                    'error' => false
                ]
            );
        } catch (\Exception $e) {
            return $this->getErrorResponse();
        }
    }

    /**
     * @return Json
     * @since 2.0.0
     */
    private function getErrorResponse()
    {
        return $this->resultJsonFactory->create()->setData(
            [
                'success' => false,
                'error' => true,
                'error_messages' => __('Your payment has been declined. Please try again.')
            ]
        );
    }
}
