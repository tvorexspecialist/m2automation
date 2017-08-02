<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model\Config\Backend\Baseurl;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;

/**
 * Class for processing of change of Base URL.
 * @since 2.2.0
 */
class SubscriptionUpdateHandler
{
    /**
     * Flag code for a reserve counter to update subscription.
     */
    const SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE = 'analytics_link_subscription_update_reverse_counter';

    /**
     * Config path for schedule setting of update handler.
     */
    const UPDATE_CRON_STRING_PATH = "crontab/default/jobs/analytics_update/schedule/cron_expr";

    /**
     * Flag code for the previous Base URL.
     */
    const PREVIOUS_BASE_URL_FLAG_CODE = 'analytics_previous_base_url';

    /**
     * Max value for a reserve counter to update subscription.
     *
     * @var int
     * @since 2.2.0
     */
    private $attemptsInitValue = 48;

    /**
     * @var WriterInterface
     * @since 2.2.0
     */
    private $configWriter;

    /**
     * Cron expression for a update handler.
     *
     * @var string
     * @since 2.2.0
     */
    private $cronExpression = '0 * * * *';

    /**
     * @var FlagManager
     * @since 2.2.0
     */
    private $flagManager;

    /**
     * @var ReinitableConfigInterface
     * @since 2.2.0
     */
    private $reinitableConfig;

    /**
     * @var AnalyticsToken
     * @since 2.2.0
     */
    private $analyticsToken;

    /**
     * @param AnalyticsToken $analyticsToken
     * @param FlagManager $flagManager
     * @param ReinitableConfigInterface $reinitableConfig
     * @param WriterInterface $configWriter
     * @since 2.2.0
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        FlagManager $flagManager,
        ReinitableConfigInterface $reinitableConfig,
        WriterInterface $configWriter
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->flagManager = $flagManager;
        $this->reinitableConfig = $reinitableConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * Activate process of subscription update handling.
     *
     * @param string $url
     * @return bool
     * @since 2.2.0
     */
    public function processUrlUpdate(string $url)
    {
        if ($this->analyticsToken->isTokenExist()) {
            if (!$this->flagManager->getFlagData(self::PREVIOUS_BASE_URL_FLAG_CODE)) {
                $this->flagManager->saveFlag(self::PREVIOUS_BASE_URL_FLAG_CODE, $url);
            }

            $this->flagManager
                ->saveFlag(self::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE, $this->attemptsInitValue);
            $this->configWriter->save(self::UPDATE_CRON_STRING_PATH, $this->cronExpression);
            $this->reinitableConfig->reinit();
        }

        return true;
    }
}
