<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Ui\Component\Report\Filters\Type;

/**
 * Class DateRange
 * @since 2.2.0
 */
class DateRange extends \Magento\Ui\Component\Filters\Type\Date
{
    /**
     * Braintree date format
     *
     * @var string
     * @since 2.2.0
     */
    protected static $dateFormat = 'Y-m-d\TH:i:00O';
}
