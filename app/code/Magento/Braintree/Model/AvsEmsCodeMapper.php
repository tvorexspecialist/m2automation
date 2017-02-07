<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Braintree\Gateway\Response\PaymentDetailsHandler;
use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Processes AVS codes mapping from Braintree transaction to
 * electronic merchant systems standard.
 *
 * @see https://developers.braintreepayments.com/reference/response/transaction
 * @see http://www.emsecommerce.net/avs_cvv2_response_codes.htm
 */
class AvsEmsCodeMapper implements PaymentVerificationInterface
{
    /**
     * List of mapping AVS codes
     *
     * @var array
     */
    private static $avsMap = [
        'MM' => 'Y',
        'NM' => 'A',
        'MN' => 'Z',
        'NN' => 'N',
        'UU' => 'U',
        'II' => 'U',
        'AA' => 'E'
    ];

    /**
     * Gets payment AVS verification code.
     * Returns null if payment does not contain any AVS details.
     *
     * @return string|null
     */
    public function getCode(OrderPaymentInterface $orderPayment)
    {
        $additionalInfo = $orderPayment->getAdditionalInformation();
        if (empty($additionalInfo[PaymentDetailsHandler::AVS_POSTAL_RESPONSE_CODE]) ||
            empty($additionalInfo[PaymentDetailsHandler::AVS_STREET_ADDRESS_RESPONSE_CODE])
        ) {
            return null;
        }
        $streetCode = $additionalInfo[PaymentDetailsHandler::AVS_STREET_ADDRESS_RESPONSE_CODE];
        $zipCode = $additionalInfo[PaymentDetailsHandler::AVS_POSTAL_RESPONSE_CODE];
        $key = $zipCode . $streetCode;
        return isset(self::$avsMap[$key]) ? self::$avsMap[$key] : null;
    }
}
