<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Helper;

use Magento\Payment\Gateway\Helper;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Class SubjectReader
 */
class SubjectReader
{
    /**
     * Reads response object from subject
     *
     * @param array $subject
     * @return object
     */
    public function readResponseObject(array $subject)
    {
        $response = Helper\SubjectReader::readResponse($subject);
        if (!isset($response['object']) || !is_object($response['object'])) {
            throw new \InvalidArgumentException('Response object does not exist');
        }

        return $response['object'];
    }

    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Reads transaction from subject
     *
     * @param array $subject
     * @return \Braintree\Transaction
     */
    public function readTransaction(array $subject)
    {
        if (!isset($subject['object']) || !is_object($subject['object'])) {
            throw new \InvalidArgumentException('Response object does not exist');
        }

        if (!isset($subject['object']->transaction)
            && !$subject['object']->transaction instanceof \Braintree\Transaction
        ) {
            throw new \InvalidArgumentException('The object is not a class \Braintree\Transaction.');
        }

        return $subject['object']->transaction;
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }

    /**
     * Reads customer id from subject
     * @param array $subject
     * @return int
     */
    public function readCustomerId(array $subject)
    {
        if (empty($subject['customerId'])) {
            throw new \InvalidArgumentException('The "customerId" field does not exists');
        }

        return (int)$subject['customerId'];
    }

    /**
     * Reads public hash from subject
     *
     * @param array $subject
     * @return string
     */
    public function readPublicHash(array $subject)
    {
        if (empty($subject['publicHash'])) {
            throw new \InvalidArgumentException('The "publicHash" field does not exists');
        }

        return $subject['publicHash'];
    }
}
