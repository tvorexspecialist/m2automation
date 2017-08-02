<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Data;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Provides scoped configuration
 * @api
 * @since 2.0.0
 */
class Scoped extends \Magento\Framework\Config\Data
{
    /**
     * Configuration scope resolver
     *
     * @var \Magento\Framework\Config\ScopeInterface
     * @since 2.0.0
     */
    protected $_configScope;

    /**
     * Configuration reader
     *
     * @var \Magento\Framework\Config\ReaderInterface
     * @since 2.0.0
     */
    protected $_reader;

    /**
     * Configuration cache
     *
     * @var \Magento\Framework\Config\CacheInterface
     * @since 2.0.0
     */
    protected $_cache;

    /**
     * Cache tag
     *
     * @var string
     * @since 2.0.0
     */
    protected $_cacheId;

    /**
     * Scope priority loading scheme
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_scopePriorityScheme = [];

    /**
     * Loaded scopes
     *
     * @var array
     * @since 2.0.0
     */
    protected $_loadedScopes = [];

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\ReaderInterface $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Config\ReaderInterface $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId,
        SerializerInterface $serializer = null
    ) {
        $this->_reader = $reader;
        $this->_configScope = $configScope;
        $this->_cache = $cache;
        $this->_cacheId = $cacheId;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * Get config value by key
     *
     * @param string $path
     * @param mixed $default
     * @return array|mixed|null
     * @since 2.0.0
     */
    public function get($path = null, $default = null)
    {
        $this->_loadScopedData();
        return parent::get($path, $default);
    }

    /**
     * Load data for current scope
     *
     * @return void
     * @since 2.0.0
     */
    protected function _loadScopedData()
    {
        $scope = $this->_configScope->getCurrentScope();
        if (false == isset($this->_loadedScopes[$scope])) {
            if (false == in_array($scope, $this->_scopePriorityScheme)) {
                $this->_scopePriorityScheme[] = $scope;
            }
            foreach ($this->_scopePriorityScheme as $scopeCode) {
                if (false == isset($this->_loadedScopes[$scopeCode])) {
                    if ($scopeCode !== 'primary' && ($data = $this->_cache->load($scopeCode . '::' . $this->_cacheId))
                    ) {
                        $data = $this->serializer->unserialize($data);
                    } else {
                        $data = $this->_reader->read($scopeCode);
                        if ($scopeCode !== 'primary') {
                            $this->_cache->save(
                                $this->serializer->serialize($data),
                                $scopeCode . '::' . $this->_cacheId
                            );
                        }
                    }
                    $this->merge($data);
                    $this->_loadedScopes[$scopeCode] = true;
                }
                if ($scopeCode == $scope) {
                    break;
                }
            }
        }
    }
}
