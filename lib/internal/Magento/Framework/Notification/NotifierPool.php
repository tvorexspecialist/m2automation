<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Notification;

/**
 * Default notifiers. Iterates through all registered notifiers to process message
 *
 * Class NotifierPool
 * @since 2.0.0
 */
class NotifierPool implements NotifierInterface
{
    /**
     * @var NotifierList
     * @since 2.0.0
     */
    protected $notifierList;

    /**
     * @param NotifierList $notifierList
     * @since 2.0.0
     */
    public function __construct(NotifierList $notifierList)
    {
        $this->notifierList = $notifierList;
    }

    /**
     * Add new message
     *
     * @param int $severity
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     * @since 2.0.0
     */
    public function add($severity, $title, $description, $url = '', $isInternal = true)
    {
        foreach ($this->notifierList->asArray() as $notifier) {
            $notifier->add($severity, $title, $description, $url, $isInternal);
        }
        return $this;
    }

    /**
     * Add critical severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     * @since 2.0.0
     */
    public function addCritical($title, $description, $url = '', $isInternal = true)
    {
        foreach ($this->notifierList->asArray() as $notifier) {
            $notifier->addCritical($title, $description, $url, $isInternal);
        }
        return $this;
    }

    /**
     * Add major severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     * @since 2.0.0
     */
    public function addMajor($title, $description, $url = '', $isInternal = true)
    {
        foreach ($this->notifierList->asArray() as $notifier) {
            $notifier->addMajor($title, $description, $url, $isInternal);
        }
        return $this;
    }

    /**
     * Add minor severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     * @since 2.0.0
     */
    public function addMinor($title, $description, $url = '', $isInternal = true)
    {
        foreach ($this->notifierList->asArray() as $notifier) {
            $notifier->addMinor($title, $description, $url, $isInternal);
        }
        return $this;
    }

    /**
     * Add notice
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     * @since 2.0.0
     */
    public function addNotice($title, $description, $url = '', $isInternal = true)
    {
        foreach ($this->notifierList->asArray() as $notifier) {
            $notifier->addNotice($title, $description, $url, $isInternal);
        }
        return $this;
    }
}
