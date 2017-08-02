<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\CronJob;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class AggregateSalesReportRefundedData
 * @since 2.0.0
 */
class AggregateSalesReportRefundedData
{
    /**
     * @var ResolverInterface
     * @since 2.0.0
     */
    protected $localeResolver;

    /**
     * @var TimezoneInterface
     * @since 2.0.0
     */
    protected $localeDate;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\RefundedFactory
     * @since 2.0.0
     */
    protected $refundedFactory;

    /**
     * @param ResolverInterface $localeResolver
     * @param TimezoneInterface $timezone
     * @param \Magento\Sales\Model\ResourceModel\Report\RefundedFactory $refundedFactory
     * @since 2.0.0
     */
    public function __construct(
        ResolverInterface $localeResolver,
        TimezoneInterface $timezone,
        \Magento\Sales\Model\ResourceModel\Report\RefundedFactory $refundedFactory
    ) {
        $this->localeResolver = $localeResolver;
        $this->localeDate = $timezone;
        $this->refundedFactory = $refundedFactory;
    }

    /**
     * Refresh sales refunded report statistics for last day
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->localeResolver->emulate(0);
        $currentDate = $this->localeDate->date();
        $date = $currentDate->sub(new \DateInterval('PT25H'));
        $this->refundedFactory->create()->aggregate($date);
        $this->localeResolver->revert();
    }
}
