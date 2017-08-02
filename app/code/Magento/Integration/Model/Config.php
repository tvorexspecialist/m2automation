<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Integration\Model\Cache\Type;

/**
 * Integration Config Model.
 *
 * This is a parent class for storing information about Integrations.
 * @deprecated 2.1.0
 * @since 2.0.0
 */
class Config
{
    const CACHE_ID = 'integration';

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     * @since 2.0.0
     */
    protected $_configCacheType;

    /**
     * @var \Magento\Integration\Model\Config\Reader
     * @since 2.0.0
     */
    protected $_configReader;

    /**
     * Array of integrations
     *
     * @var array
     * @since 2.0.0
     */
    protected $_integrations;

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param Cache\Type $configCacheType
     * @param Config\Reader $configReader
     * @param SerializerInterface $serializer
     * @since 2.0.0
     */
    public function __construct(
        Cache\Type $configCacheType,
        Config\Reader $configReader,
        SerializerInterface $serializer = null
    ) {
        $this->_configCacheType = $configCacheType;
        $this->_configReader = $configReader;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * Return integrations loaded from cache if enabled or from files merged previously
     *
     * @return array
     * @api
     * @since 2.0.0
     */
    public function getIntegrations()
    {
        if (null === $this->_integrations) {
            $integrations = $this->_configCacheType->load(self::CACHE_ID);
            if ($integrations && is_string($integrations)) {
                $this->_integrations = $this->serializer->unserialize($integrations);
            } else {
                $this->_integrations = $this->_configReader->read();
                $this->_configCacheType->save(
                    $this->serializer->serialize($this->_integrations),
                    self::CACHE_ID,
                    [Type::CACHE_TAG]
                );
            }
        }
        return $this->_integrations;
    }
}
