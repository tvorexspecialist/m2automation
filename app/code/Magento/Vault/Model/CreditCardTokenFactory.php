<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

/**
 * Class CreditCardTokenFactory
 * @deprecated
 * @see PaymentTokenFactory
 */
class CreditCardTokenFactory extends AbstractPaymentTokenFactory
{
    /**
     * @var string
     */
    const TOKEN_TYPE_CREDIT_CARD = 'card';

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return self::TOKEN_TYPE_CREDIT_CARD;
    }
}
