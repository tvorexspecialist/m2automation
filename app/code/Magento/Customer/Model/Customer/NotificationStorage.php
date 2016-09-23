<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Json\JsonInterface;

class NotificationStorage
{
    const UPDATE_CUSTOMER_SESSION = 'update_customer_session';

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @param FrontendInterface $cache
     */

    /**
     * @var JsonInterface
     */
    private $json;

    /**
     * NotificationStorage constructor.
     * @param FrontendInterface $cache
     */
    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Add notification in cache
     *
     * @param string $notificationType
     * @param string $customerId
     * @return void
     */
    public function add($notificationType, $customerId)
    {
        $this->cache->save(
            $this->getJson()->encode([
                'customer_id' => $customerId,
                'notification_type' => $notificationType
            ]),
            $this->getCacheKey($notificationType, $customerId)
        );
    }

    /**
     * Check whether notification is exists in cache
     *
     * @param string $notificationType
     * @param string $customerId
     * @return bool
     */
    public function isExists($notificationType, $customerId)
    {
        return $this->cache->test($this->getCacheKey($notificationType, $customerId));
    }

    /**
     * Remove notification from cache
     *
     * @param string $notificationType
     * @param string $customerId
     * @return void
     */
    public function remove($notificationType, $customerId)
    {
        $this->cache->remove($this->getCacheKey($notificationType, $customerId));
    }

    /**
     * Retrieve cache key
     *
     * @param string $notificationType
     * @param string $customerId
     * @return string
     */
    private function getCacheKey($notificationType, $customerId)
    {
        return 'notification_' . $notificationType . '_' . $customerId;
    }

    /**
     * Get json encoder/decoder
     *
     * @return JsonInterface
     * @deprecated
     */
    private function getJson()
    {
        if ($this->json === null) {
            $this->json = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(JsonInterface::class);
        }
        return $this->json;
    }
}
