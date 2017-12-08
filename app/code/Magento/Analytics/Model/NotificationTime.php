<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\FlagManager;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * Class NotificationTime
 *
 * Manage access to notification time flag
 *
 */
class NotificationTime
{
    const NOTIFICATION_TIME = 'analytics_notification_time_admin_';

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * NotificationTime constructor.
     *
     * @param FlagManager $flagManager
     * @param UserContextInterface $userContext
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        FlagManager $flagManager,
        UserContextInterface $userContext,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->flagManager = $flagManager;
        $this->userContext = $userContext;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Stores last notification time
     *
     * @return bool
     */
    public function storeLastTimeNotificationForCurrentUser()
    {
        $flagCode = self::NOTIFICATION_TIME . $this->userContext->getUserId();
        $dateTime = $this->dateTimeFactory->create();
        return $this->flagManager->saveFlag($flagCode, $dateTime->getTimestamp());
    }

    /**
     * Returns last time when merchant was notified about Analytic services
     *
     * @return int
     */
    public function getLastTimeNotificationForCurrentUser()
    {
        return $this->flagManager->getFlagData(self::NOTIFICATION_TIME . $this->userContext->getUserId());
    }

    /**
     * Remove last notification time flag.
     *
     * @return bool
     */
    public function unsetLastTimeNotificationValueForCurrentUser()
    {
        return $this->flagManager->deleteFlag(self::NOTIFICATION_TIME . $this->userContext->getUserId());
    }
}
