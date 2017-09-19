<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Controller\Button;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class PlaceOrder extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;
    /**
     * @var \Magento\OneTouchOrdering\Model\PlaceOrder
     */
    private $placeOrder;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\OneTouchOrdering\Model\PlaceOrder $placeOrder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->placeOrder = $placeOrder;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $product = $this->_initProduct();
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $params = $this->getRequest()->getParams();
        try {
            $orderId = $this->placeOrder->placeOrder($product, $params);
        } catch (NoSuchEntityException $e) {
            $errorMsg = __('Something went wrong while processing your order. Please try again later.');
            $this->messageManager->addErrorMessage($errorMsg);
            $result->setData([
                'response' => $errorMsg
            ]);
            return $result;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $result->setData([
                'response' => $e->getMessage()
            ]);
            return $result;
        }

        $order = $this->orderRepository->get($orderId);
        $message = __('Your order number is: %1.', $order->getIncrementId());
        $this->messageManager->addSuccessMessage($message);
        $result->setData([
            'response' => $message
        ]);
        return $result;
    }

    private function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            return $this->productRepository->getById($productId, false, $storeId);
        }
        throw new NoSuchEntityException(__('Requested product doesn\'t exist'));
    }
}
