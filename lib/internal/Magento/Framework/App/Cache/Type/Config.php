<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Cache\Type;

use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\Config\CacheInterface;

/**
 * System / Cache Management / Cache type "Configuration"
 * @since 2.0.0
 */
class Config extends TagScope implements CacheInterface
{
    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'config';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'CONFIG';

    /**
     * @var \Magento\Framework\App\Cache\Type\FrontendPool
     * @since 2.0.0
     */
    private $cacheFrontendPool;

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool)
    {
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * Retrieve cache frontend instance being decorated
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     * @since 2.0.0
     */
    protected function _getFrontend()
    {
        $frontend = parent::_getFrontend();
        if (!$frontend) {
            $frontend = $this->cacheFrontendPool->get(self::TYPE_IDENTIFIER);
            $this->setFrontend($frontend);
        }
        return $frontend;
    }

    /**
     * Retrieve cache tag name
     *
     * @return string
     * @since 2.0.0
     */
    public function getTag()
    {
        return self::CACHE_TAG;
    }
}
