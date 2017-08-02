<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\Config\Source;

/**
 * Class \Magento\Cron\Model\Config\Source\Frequency
 *
 * @since 2.0.0
 */
class Frequency implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected static $_options;

    const CRON_DAILY = 'D';

    const CRON_WEEKLY = 'W';

    const CRON_MONTHLY = 'M';

    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if (!self::$_options) {
            self::$_options = [
                ['label' => __('Daily'), 'value' => self::CRON_DAILY],
                ['label' => __('Weekly'), 'value' => self::CRON_WEEKLY],
                ['label' => __('Monthly'), 'value' => self::CRON_MONTHLY],
            ];
        }
        return self::$_options;
    }
}
