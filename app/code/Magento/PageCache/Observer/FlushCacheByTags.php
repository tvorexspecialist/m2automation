<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;

class FlushCacheByTags implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\PageCache\Cache
     *
     * @deprecated
     */
    protected $_cache;

    /**
     * Application config object
     *
     * @var \Magento\PageCache\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\PageCache\Model\Cache\Type
     */
    private $fullPageCache;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\PageCache\Cache $cache
     */
    public function __construct(\Magento\PageCache\Model\Config $config, \Magento\Framework\App\PageCache\Cache $cache)
    {
        $this->_config = $config;
        $this->_cache = $cache;
    }

    /**
     * If Built-In caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_config->getType() == \Magento\PageCache\Model\Config::BUILT_IN && $this->_config->isEnabled()) {
            $object = $observer->getEvent()->getObject();
            if ($object instanceof \Magento\Framework\DataObject\IdentityInterface) {
                $tags = $object->getIdentities();
                if (!empty($tags)) {
                    $this->getCache()->clean(\Zend_Cache::CLEANING_MODE_ALL, array_unique($tags));
                }
            }
        }
    }

    /**
     * TODO: Workaround to support backwards compatibility, will rework to use Dependency Injection in MAGETWO-49547
     *
     * @return \Magento\PageCache\Model\Cache\Type
     */
    private function getCache()
    {
        if (!$this->fullPageCache) {
            $this->fullPageCache = ObjectManager::getInstance()->get('\Magento\PageCache\Model\Cache\Type');
        }
        return $this->fullPageCache;
    }
}
