<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Implements NotSyncedDataProviderInterface as composite
 * @since 2.2.0
 */
class NotSyncedDataProvider implements NotSyncedDataProviderInterface
{
    /**
     * @var NotSyncedDataProviderInterface[]
     * @since 2.2.0
     */
    private $providers;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $providers
     * @since 2.2.0
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $providers = []
    ) {
        $this->providers = $tmapFactory->create(
            [
                'array' => $providers,
                'type' => NotSyncedDataProviderInterface::class
            ]
        );
    }

    /**
     * @inheritDoc
     * @since 2.2.0
     */
    public function getIds($mainTableName, $gridTableName)
    {
        $result = [];
        foreach ($this->providers as $provider) {
            $result = array_merge($result, $provider->getIds($mainTableName, $gridTableName));
        }

        return array_unique($result);
    }
}
