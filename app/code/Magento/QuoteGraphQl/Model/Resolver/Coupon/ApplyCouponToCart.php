<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\Coupon;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * {@inheritdoc}
 */
class ApplyCouponToCart implements ResolverInterface
{
    /**
     * @var CouponManagementInterface
     */
    private $couponManagement;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @param ValueFactory $valueFactory
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        ValueFactory $valueFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CouponManagementInterface $couponManagement
    ) {
        $this->valueFactory = $valueFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->couponManagement = $couponManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null) : Value
    {
        $maskedCartId = $args['input']['cart_id'];
        $couponCode = $args['input']['coupon_code'];

        if (!$maskedCartId || !$couponCode) {
            throw new GraphQlInputException(__('Required parameter is missing'));
        }

        // FIXME: use resource model instead
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($maskedCartId, 'masked_id');
        if (!$quoteIdMask->getId()) {
            throw new GraphQlNoSuchEntityException(__('No cart with provided ID found'));
        }

        $cartId = $quoteIdMask->getQuoteId();

        /* Check current cart does not have coupon code applied */
        $appliedCouponCode = $this->couponManagement->get($cartId);
        if (!empty($appliedCouponCode)) {
            throw new GraphQlInputException(
                __('A coupon is already applied to the cart. Please remove it to apply another')
            );
        }

        try {
            $this->couponManagement->set($cartId, $couponCode);
        } catch (\Exception $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        $data['cart']['applied_coupon'] = [
            'code' => $couponCode
        ];

        $result = function () use ($data) {
            return $data;
        };

        return $this->valueFactory->create($result);
    }
}
