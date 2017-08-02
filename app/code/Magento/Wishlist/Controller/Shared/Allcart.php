<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Shared;

use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Model\ItemCarrier;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Wishlist\Controller\Shared\Allcart
 *
 * @since 2.0.0
 */
class Allcart extends \Magento\Framework\App\Action\Action
{
    /**
     * @var WishlistProvider
     * @since 2.0.0
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Wishlist\Model\ItemCarrier
     * @since 2.0.0
     */
    protected $itemCarrier;

    /**
     * @param Context $context
     * @param WishlistProvider $wishlistProvider
     * @param ItemCarrier $itemCarrier
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        WishlistProvider $wishlistProvider,
        ItemCarrier $itemCarrier
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->itemCarrier = $itemCarrier;
        parent::__construct($context);
    }

    /**
     * Add all items from wishlist to shopping cart
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }
        $redirectUrl = $this->itemCarrier->moveAllToCart($wishlist, $this->getRequest()->getParam('qty'));
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
