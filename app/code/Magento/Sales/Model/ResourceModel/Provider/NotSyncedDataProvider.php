<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Implements NotSyncedDataProviderInterface as composite
 */
class NotSyncedDataProvider implements NotSyncedDataProviderInterface
{
    /**
     * @var NotSyncedDataProviderInterface[]
     */
    private $providers;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $providers
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
     */
    public function getIds($mainTableName, $gridTableName)
    {
        $result = [];
        foreach ($this->providers as $provider) {
            $result = array_merge($result, $provider->get($mainTableName, $gridTableName));
        }

        return array_unique($result);
    }
}
