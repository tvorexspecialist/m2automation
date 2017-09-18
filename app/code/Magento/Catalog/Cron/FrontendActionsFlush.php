<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Cron;

use Magento\Catalog\Model\FrontendStorageConfigurationInterface;
use Magento\Catalog\Model\FrontendStorageConfigurationPool;
use Magento\Catalog\Model\ResourceModel\ProductFrontendAction;

/**
 * Find deprecated frontend actions (@see \Magento\Catalog\Api\Data\ProductFrontendActionInterface)
 * Frontend actions deprecates by lifetime.
 * For each scope we have own lifetime.
 */
class FrontendActionsFlush
{
    /**
     * @var ProductFrontendAction
     */
    private $productFrontendActionResource;

    /**
     * @var FrontendStorageConfigurationPool
     */
    private $frontendStorageConfigurationPool;

    /**
     * @param ProductFrontendAction $productFrontendActionResource
     * @param FrontendStorageConfigurationPool $frontendStorageConfigurationPool
     */
    public function __construct(
        ProductFrontendAction $productFrontendActionResource,
        FrontendStorageConfigurationPool $frontendStorageConfigurationPool
    ) {
        $this->productFrontendActionResource = $productFrontendActionResource;
        $this->frontendStorageConfigurationPool = $frontendStorageConfigurationPool;
    }

    /**
     * Find lifetime in configuration. Configuration is hold in Stores Configuration
     * Also this configuration is generated by:
     * @see \Magento\Catalog\Model\Widget\RecentlyViewedStorageConfiguration
     *
     * @param string $namespace
     * @return int
     */
    private function getLifeTimeByNamespace($namespace)
    {
        $configurationObject = $this->frontendStorageConfigurationPool->get($namespace);
        if ($configurationObject) {
            $configuration = $configurationObject->get();
        } else {
            $configuration = [
                'lifetime' => FrontendStorageConfigurationInterface::DEFAULT_LIFETIME
            ];
        }

        return isset($configuration['lifetime']) ?
            (int) $configuration['lifetime'] : FrontendStorageConfigurationInterface::DEFAULT_LIFETIME;
    }

    /**
     * Retrieve unique namespaces FROM frontend actions. Namespace can be represented by:
     * recently_viewed_product, recently_compared_product, etc...
     *
     * @return array
     */
    private function getUniqueNamespaces()
    {
        $adapter = $this->productFrontendActionResource->getConnection();
        $query = $adapter->select()
            ->from($this->productFrontendActionResource->getMainTable(), ['action_id', 'type_id'])
            ->group('type_id');

        return $adapter->fetchPairs($query);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $adapter = $this->productFrontendActionResource->getConnection();

        foreach ($this->getUniqueNamespaces() as $namespace) {
            $lifeTime = $this->getLifeTimeByNamespace($namespace);

            $where = [
                $adapter->quoteInto('added_at < ?', time() - $lifeTime)
            ];

            $adapter->delete($this->productFrontendActionResource->getMainTable(), $where);
        }
    }
}
