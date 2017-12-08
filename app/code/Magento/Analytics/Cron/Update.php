<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Connector;
use Magento\Framework\FlagManager;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Executes by cron schedule in case base url was changed
 * @since 2.2.0
 */
class Update
{
    /**
     * @var Connector
     * @since 2.2.0
     */
    private $connector;

    /**
     * @var WriterInterface
     * @since 2.2.0
     */
    private $configWriter;

    /**
     * @var ReinitableConfigInterface
     * @since 2.2.0
     */
    private $reinitableConfig;

    /**
     * @var FlagManager
     * @since 2.2.0
     */
    private $flagManager;

    /**
     * @var AnalyticsToken
     * @since 2.2.0
     */
    private $analyticsToken;

    /**
     * @param Connector $connector
     * @param WriterInterface $configWriter
     * @param ReinitableConfigInterface $reinitableConfig
     * @param FlagManager $flagManager
     * @param AnalyticsToken $analyticsToken
     * @since 2.2.0
     */
    public function __construct(
        Connector $connector,
        WriterInterface $configWriter,
        ReinitableConfigInterface $reinitableConfig,
        FlagManager $flagManager,
        AnalyticsToken $analyticsToken
    ) {
        $this->connector = $connector;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
        $this->flagManager = $flagManager;
        $this->analyticsToken = $analyticsToken;
    }

    /**
     * Execute scheduled update operation
     *
     * @return bool
     * @since 2.2.0
     */
    public function execute()
    {
        $result = false;
        $attemptsCount = $this->flagManager
            ->getFlagData(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE);

        if ($attemptsCount) {
            $attemptsCount -= 1;
            $result = $this->connector->execute('update');
        }

        if ($result || ($attemptsCount <= 0) || (!$this->analyticsToken->isTokenExist())) {
            $this->flagManager
                ->deleteFlag(SubscriptionUpdateHandler::SUBSCRIPTION_UPDATE_REVERSE_COUNTER_FLAG_CODE);
            $this->flagManager->deleteFlag(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE);
            $this->configWriter->delete(SubscriptionUpdateHandler::UPDATE_CRON_STRING_PATH);
            $this->reinitableConfig->reinit();
        }

        return $result;
    }
}
