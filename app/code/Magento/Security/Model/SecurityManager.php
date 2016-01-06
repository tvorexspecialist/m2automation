<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

use Magento\Framework\Exception\SecurityViolationException;

/**
 * Security Control Manager Model
 */
class SecurityManager
{
    /**
     * Security control records time life
     */
    const SECURITY_CONTROL_RECORDS_LIFE_TIME =  86400;

    /**
     * @var \Magento\Security\Helper\SecurityConfig
     */
    protected $securityConfig;

    /**
     * @var \Magento\Security\Model\PasswordResetRequestEventFactory
     */
    protected $passwordResetRequestEventModelFactory;

    /**
     * @var ResourceModel\PasswordResetRequestEvent
     */
    protected $passwordResetRequestEventResource;

    /**
     * @var array
     */
    protected $securityCheckers;

    /**
     * SecurityManager constructor.
     * @param \Magento\Security\Helper\SecurityConfig $securityConfig
     * @param \Magento\Security\Model\PasswordResetRequestEventFactory $passwordResetRequestEventModelFactory
     * @param ResourceModel\PasswordResetRequestEvent $passwordResetRequestEventResource
     * @param array $securityCheckers
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Security\Helper\SecurityConfig $securityConfig,
        \Magento\Security\Model\PasswordResetRequestEventFactory $passwordResetRequestEventModelFactory,
        \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent $passwordResetRequestEventResource,
        $securityCheckers = []
    ) {
        $this->securityConfig = $securityConfig;
        $this->passwordResetRequestEventModelFactory = $passwordResetRequestEventModelFactory;
        $this->passwordResetRequestEventResource = $passwordResetRequestEventResource;
        $this->securityCheckers = $securityCheckers;

        foreach ($this->securityCheckers as $checker) {
            if (!($checker instanceof \Magento\Security\Model\SecurityChecker\SecurityCheckerInterface)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Incorrect Security Checker class. It has to implement SecurityCheckerInterface')
                );
            }
        }
    }

    /**
     * @param int $requestType
     * @param string|null $accountReference
     * @param int|null $longIp
     * @return $this
     * @throws SecurityViolationException
     */
    public function performSecurityCheck($requestType, $accountReference = null, $longIp = null)
    {
        if (null === $longIp) {
            $longIp = $this->securityConfig->getRemoteIp();
        }
        foreach ($this->securityCheckers as $checker) {
            $checker->check($requestType, $accountReference, $longIp);
        }

        $this->createNewPasswordResetRequestEventRecord($requestType, $accountReference, $longIp);

        return $this;
    }

    /**
     * @param int $requestType
     * @param string|null $accountReference
     * @param int $longIp
     * @return PasswordResetRequestEvent
     */
    protected function createNewPasswordResetRequestEventRecord($requestType, $accountReference, $longIp)
    {
        /** @var \Magento\Security\Model\PasswordResetRequestEvent $passwordResetRequestEventModel */
        $passwordResetRequestEventModel = $this->passwordResetRequestEventModelFactory->create();
        $passwordResetRequestEventModel->setRequestType($requestType)
            ->setAccountReference($accountReference)
            ->setIp($longIp)
            ->save();

        return $passwordResetRequestEventModel;
    }

    /**
     * Clean expired Admin Sessions
     *
     * @return $this
     */
    public function cleanExpiredRecords()
    {
        $this->passwordResetRequestEventResource->deleteRecordsOlderThen(
            $this->securityConfig->getCurrentTimestamp() - self::SECURITY_CONTROL_RECORDS_LIFE_TIME
        );

        return $this;
    }
}
