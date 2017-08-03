<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Model\Cart\Controller;

/**
 * Class \Magento\Multishipping\Model\Cart\Controller\CartPlugin
 *
 * @since 2.0.9
 */
class CartPlugin
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     * @since 2.0.9
     */
    private $cartRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.9
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     * @since 2.2.0
     */
    private $addressRepository;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @since 2.0.9
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart $subject
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.9
     */
    public function beforeDispatch(
        \Magento\Checkout\Controller\Cart $subject,
        \Magento\Framework\App\RequestInterface $request
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        // Clear shipping addresses and item assignments after MultiShipping flow
        if ($quote->isMultipleShippingAddresses()) {
            foreach ($quote->getAllShippingAddresses() as $address) {
                $quote->removeAddress($address->getId());
            }

            $shippingAddress = $quote->getShippingAddress();
            $defaultShipping = $quote->getCustomer()->getDefaultShipping();
            if ($defaultShipping) {
                $defaultCustomerAddress = $this->addressRepository->getById(
                    $defaultShipping
                );
                $shippingAddress->importCustomerAddressData($defaultCustomerAddress);
            }
            $this->cartRepository->save($quote);
        }
    }
}
