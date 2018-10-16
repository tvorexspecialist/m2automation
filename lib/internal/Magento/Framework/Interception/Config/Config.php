<?php
/**
 * Interception config. Responsible for providing list of plugins configured for instance
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Config;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Serialize;

class Config implements \Magento\Framework\Interception\ConfigInterface
{
    /**
     * Type configuration
     *
     * @var \Magento\Framework\Interception\ObjectManager\ConfigInterface
     */
    protected $_omConfig;

    /**
     * Class relations info
     *
     * @var \Magento\Framework\ObjectManager\RelationsInterface
     */
    protected $_relations;

    /**
     * List of interceptable classes
     *
     * @var \Magento\Framework\ObjectManager\DefinitionInterface
     */
    protected $_classDefinitions;

    /**
     * Cache
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * Cache identifier
     *
     * @var string
     */
    protected $_cacheId;

    /**
     * Configuration reader
     *
     * @var \Magento\Framework\Config\ReaderInterface
     */
    protected $_reader;

    /**
     * Inherited list of intercepted types
     *
     * @var array
     */
    protected $_intercepted = [];

    /**
     * List of class types that can not be pluginized
     *
     * @var array
     */
    protected $_serviceClassTypes = ['Interceptor'];

    /**
     * @var \Magento\Framework\Config\ScopeListInterface
     */
    protected $_scopeList;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Setup\Module\Di\Compiler\Config\Writer\Filesystem
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled
     */
    private $compiledLoader;

    /**
     * Config constructor
     *
     * @param \Magento\Framework\Config\ReaderInterface $reader
     * @param \Magento\Framework\Config\ScopeListInterface $scopeList
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\ObjectManager\RelationsInterface $relations
     * @param \Magento\Framework\Interception\ObjectManager\ConfigInterface $omConfig
     * @param \Magento\Framework\ObjectManager\DefinitionInterface $classDefinitions
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     * @param \Magento\Setup\Module\Di\Compiler\Config\Writer\Filesystem $configWriter
     * @param \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled $compiledLoader
     */
    public function __construct(
        \Magento\Framework\Config\ReaderInterface $reader,
        \Magento\Framework\Config\ScopeListInterface $scopeList,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\ObjectManager\RelationsInterface $relations,
        \Magento\Framework\Interception\ObjectManager\ConfigInterface $omConfig,
        \Magento\Framework\ObjectManager\DefinitionInterface $classDefinitions,
        $cacheId = 'interception',
        SerializerInterface $serializer = null,
        \Magento\Setup\Module\Di\Compiler\Config\Writer\Filesystem $configWriter = null,
        \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled $compiledLoader = null
    ) {
        $this->_omConfig = $omConfig;
        $this->_relations = $relations;
        $this->_classDefinitions = $classDefinitions;
        $this->_cache = $cache;
        $this->_cacheId = $cacheId;
        $this->_reader = $reader;
        $this->_scopeList = $scopeList;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Serialize::class);
        $this->configWriter = $configWriter ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Setup\Module\Di\Compiler\Config\Writer\Filesystem::class);
        $this->compiledLoader = $compiledLoader ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\App\ObjectManager\ConfigLoader\Compiled::class);
        $intercepted = $this->loadIntercepted();
        if ($intercepted !== false) {
            $this->_intercepted = $intercepted;
        } else {
            $this->initializeUncompiled($this->_classDefinitions->getClasses());
        }
    }

    /**
     * Initialize interception config
     *
     * @param array $classDefinitions
     * @return void
     */
    public function initialize($classDefinitions = [])
    {
        $this->generateIntercepted($classDefinitions);

        $this->configWriter->write($this->_cacheId, $this->_intercepted);
    }

    /**
     * Process interception inheritance
     *
     * @param string $type
     * @return bool
     */
    protected function _inheritInterception($type)
    {
        $type = ltrim($type, '\\');
        if (!isset($this->_intercepted[$type])) {
            $realType = $this->_omConfig->getOriginalInstanceType($type);
            if ($type !== $realType) {
                if ($this->_inheritInterception($realType)) {
                    $this->_intercepted[$type] = true;
                    return true;
                }
            } else {
                $parts = explode('\\', $type);
                if (!in_array(end($parts), $this->_serviceClassTypes) && $this->_relations->has($type)) {
                    $relations = $this->_relations->getParents($type);
                    foreach ($relations as $relation) {
                        if ($relation && $this->_inheritInterception($relation)) {
                            $this->_intercepted[$type] = true;
                            return true;
                        }
                    }
                }
            }
            $this->_intercepted[$type] = false;
        }
        return $this->_intercepted[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function hasPlugins($type)
    {
        if (isset($this->_intercepted[$type])) {
            return $this->_intercepted[$type];
        }
        return $this->_inheritInterception($type);
    }

    /**
     * Write interception config to cache
     *
     * @param array $classDefinitions
     */
    private function initializeUncompiled($classDefinitions = [])
    {
        $this->_cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [$this->_cacheId]);

        $this->generateIntercepted($classDefinitions);

        $this->_cache->save($this->serializer->serialize($this->_intercepted), $this->_cacheId);
    }

    /**
     * Generate intercepted array to store in compiled metadata or frontend cache
     *
     * @param $classDefinitions
     */
    private function generateIntercepted($classDefinitions)
    {
        $config = [];
        foreach ($this->_scopeList->getAllScopes() as $scope) {
            $config = array_replace_recursive($config, $this->_reader->read($scope));
        }
        unset($config['preferences']);
        foreach ($config as $typeName => $typeConfig) {
            if (!empty($typeConfig['plugins'])) {
                $this->_intercepted[ltrim($typeName, '\\')] = true;
            }
        }
        foreach ($config as $typeName => $typeConfig) {
            $this->hasPlugins($typeName);
        }
        foreach ($classDefinitions as $class) {
            $this->hasPlugins($class);
        }
    }

    /**
     * Load the interception config from cache
     *
     * @return array|false
     */
    private function loadIntercepted()
    {
        if ($this->isCompiled()) {
            return $this->compiledLoader->load($this->_cacheId);
        }

        $intercepted = $this->_cache->load($this->_cacheId);
        return $intercepted ? $this->serializer->unserialize($intercepted) : false;
    }

    /**
     * Check for the compiled config with the generated metadata
     *
     * @return bool
     */
    private function isCompiled()
    {
        return file_exists(\Magento\Framework\App\ObjectManager\ConfigLoader\Compiled::getFilePath($this->_cacheId));
    }
}
