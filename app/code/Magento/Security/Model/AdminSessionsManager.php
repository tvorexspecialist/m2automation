<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory;

/**
 * Admin Sessions Manager Model
 *
 * @api
 * @since 2.1.0
 */
class AdminSessionsManager
{
    /**
     * Admin Session lifetime (sec)
     */
    const ADMIN_SESSION_LIFETIME = 86400;

    /**
     * Logout reason when current user has been locked out
     */
    const LOGOUT_REASON_USER_LOCKED = 10;

    /**
     * @var ConfigInterface
     * @since 2.1.0
     */
    protected $securityConfig;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.1.0
     */
    protected $authSession;

    /**
     * @var AdminSessionInfoFactory
     * @since 2.1.0
     */
    protected $adminSessionInfoFactory;

    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory
     * @since 2.1.0
     */
    protected $adminSessionInfoCollectionFactory;

    /**
     * @var \Magento\Security\Model\AdminSessionInfo
     * @since 2.1.0
     */
    protected $currentSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.1.0
     */
    private $dateTime;

    /**
     * @var RemoteAddress
     * @since 2.1.0
     */
    private $remoteAddress;

    /**
     * @param ConfigInterface $securityConfig
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param AdminSessionInfoFactory $adminSessionInfoFactory
     * @param CollectionFactory $adminSessionInfoCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param RemoteAddress $remoteAddress
     * @since 2.1.0
     */
    public function __construct(
        ConfigInterface $securityConfig,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Security\Model\AdminSessionInfoFactory $adminSessionInfoFactory,
        \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory $adminSessionInfoCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        RemoteAddress $remoteAddress
    ) {
        $this->securityConfig = $securityConfig;
        $this->authSession = $authSession;
        $this->adminSessionInfoFactory = $adminSessionInfoFactory;
        $this->adminSessionInfoCollectionFactory = $adminSessionInfoCollectionFactory;
        $this->dateTime = $dateTime;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * Handle all others active sessions according Sharing Account Setting
     *
     * @return $this
     * @since 2.1.0
     */
    public function processLogin()
    {
        $this->createNewSession();

        $olderThen = $this->dateTime->gmtTimestamp() - $this->securityConfig->getAdminSessionLifetime();
        if (!$this->securityConfig->isAdminAccountSharingEnabled()) {
            $result = $this->createAdminSessionInfoCollection()->updateActiveSessionsStatus(
                AdminSessionInfo::LOGGED_OUT_BY_LOGIN,
                $this->getCurrentSession()->getUserId(),
                $this->getCurrentSession()->getSessionId(),
                $olderThen
            );
            if ($result) {
                $this->getCurrentSession()->setIsOtherSessionsTerminated(true);
            }
        }

        return $this;
    }

    /**
     * Handle Prolong process
     *
     * @return $this
     * @since 2.1.0
     */
    public function processProlong()
    {
        $this->getCurrentSession()->setData(
            'updated_at',
            $this->authSession->getUpdatedAt()
        );
        $this->getCurrentSession()->save();

        return $this;
    }

    /**
     * Handle logout process
     *
     * @return $this
     * @since 2.1.0
     */
    public function processLogout()
    {
        $this->getCurrentSession()->setData(
            'status',
            AdminSessionInfo::LOGGED_OUT
        );
        $this->getCurrentSession()->save();

        return $this;
    }

    /**
     * Get current session record
     *
     * @return AdminSessionInfo
     * @since 2.1.0
     */
    public function getCurrentSession()
    {
        if (!$this->currentSession) {
            $this->currentSession = $this->adminSessionInfoFactory->create();
            $this->currentSession->load($this->authSession->getSessionId(), 'session_id');
        }

        return $this->currentSession;
    }

    /**
     * Get logout reason message by status
     *
     * @param int $statusCode
     * @return string
     * @since 2.1.0
     */
    public function getLogoutReasonMessageByStatus($statusCode)
    {
        switch ((int)$statusCode) {
            case AdminSessionInfo::LOGGED_IN:
                $reasonMessage = null;
                break;
            case AdminSessionInfo::LOGGED_OUT_BY_LOGIN:
                $reasonMessage = __(
                    'Someone logged into this account from another device or browser.'
                    .' Your current session is terminated.'
                );
                break;
            case AdminSessionInfo::LOGGED_OUT_MANUALLY:
                $reasonMessage = __(
                    'Your current session is terminated by another user of this account.'
                );
                break;
            case self::LOGOUT_REASON_USER_LOCKED:
                $reasonMessage = __(
                    'Your account is temporarily disabled.'
                );
                break;
            default:
                $reasonMessage = __('Your current session has been expired.');
                break;
        }

        return $reasonMessage;
    }

    /**
     * Get message with explanation of logout reason
     *
     * @return string
     * @since 2.1.0
     */
    public function getLogoutReasonMessage()
    {
        return $this->getLogoutReasonMessageByStatus(
            $this->getCurrentSession()->getStatus()
        );
    }

    /**
     * Get sessions for current user
     *
     * @return \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection
     * @since 2.1.0
     */
    public function getSessionsForCurrentUser()
    {
        return $this->createAdminSessionInfoCollection()
            ->filterByUser($this->authSession->getUser()->getId(), \Magento\Security\Model\AdminSessionInfo::LOGGED_IN)
            ->filterExpiredSessions($this->securityConfig->getAdminSessionLifetime())
            ->loadData();
    }

    /**
     * Logout another user sessions
     *
     * @return $this
     * @since 2.1.0
     */
    public function logoutOtherUserSessions()
    {
        $collection = $this->createAdminSessionInfoCollection()
            ->filterByUser(
                $this->authSession->getUser()->getId(),
                \Magento\Security\Model\AdminSessionInfo::LOGGED_IN,
                $this->authSession->getSessionId()
            )
            ->filterExpiredSessions($this->securityConfig->getAdminSessionLifetime())
            ->loadData();

        $collection->setDataToAll('status', \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_MANUALLY)
            ->save();

        return $this;
    }

    /**
     * Clean expired Admin Sessions
     *
     * @return $this
     * @since 2.1.0
     */
    public function cleanExpiredSessions()
    {
        $this->createAdminSessionInfoCollection()->deleteSessionsOlderThen(
            $this->dateTime->gmtTimestamp() - self::ADMIN_SESSION_LIFETIME
        );

        return $this;
    }

    /**
     * Create new record
     *
     * @return $this
     * @since 2.1.0
     */
    protected function createNewSession()
    {
        $this->adminSessionInfoFactory
            ->create()
            ->setData(
                [
                    'session_id' => $this->authSession->getSessionId(),
                    'user_id' => $this->authSession->getUser()->getId(),
                    'ip' => $this->remoteAddress->getRemoteAddress(),
                    'status' => AdminSessionInfo::LOGGED_IN
                ]
            )->save();

        return $this;
    }

    /**
     * @return \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection
     * @since 2.1.0
     */
    protected function createAdminSessionInfoCollection()
    {
        return $this->adminSessionInfoCollectionFactory->create();
    }
}
