<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;

class InvalidateVarnishObserver implements ObserverInterface
{
    /**
     * Application config object
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\CacheInvalidate\Model\PurgeCache
     */
    protected $purgeCache;

    /**
     * Invalidation tags resolver
     *
     * @var \Magento\PageCache\Model\Cache\Tag\Resolver
     */
    private $tagResolver;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\CacheInvalidate\Model\PurgeCache $purgeCache
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\CacheInvalidate\Model\PurgeCache $purgeCache
    ) {
        $this->config = $config;
        $this->purgeCache = $purgeCache;
        $this->tagResolver = $this->getTagResolver();
    }

    /**
     * If Varnish caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()) {
            $object = $observer->getEvent()->getObject();
            $bareTags = $this->tagResolver->getTags($object);

            $tags = [];
            $pattern = "((^|,)%s(,|$))";
            foreach ($bareTags as $tag) {
                $tags[] = sprintf($pattern, $tag);
            }
            if (!empty($tags)) {
                $this->purgeCache->sendPurgeRequest(implode('|', array_unique($tags)));
            }

        }
    }

    /**
     * @deprecated
     * @return \Magento\Framework\App\Cache\Tag\Resolver
     */
    private function getTagResolver()
    {
        return ObjectManager::getInstance()->get(\Magento\Framework\App\Cache\Tag\Resolver::class);
    }
}
