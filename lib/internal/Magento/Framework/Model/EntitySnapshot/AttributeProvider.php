<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\EntitySnapshot;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\ObjectManagerInterface as ObjectManager;

/**
 * Class EntitySnapshot
 */
class AttributeProvider implements AttributeProviderInterface
{
    /**
     * @var AttributeProviderInterface[]
     */
    protected $providers;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $registry;

    /**
     * @param MetadataPool $metadataPool
     * @param ObjectManager $objectManager
     * @param array $providers
     */
    public function __construct(
        MetadataPool $metadataPool,
        ObjectManager $objectManager,
        $providers = []
    ) {
        $this->metadataPool = $metadataPool;
        $this->objectManager = $objectManager;
        $this->providers = $providers;
    }

    /**
     * Returns array of fields
     *
     * @param string $entityType
     * @return array
     * @throws \Exception
     */
    public function getAttributes($entityType)
    {
        if (!isset($this->registry[$entityType])) {
            $metadata = $this->metadataPool->getMetadata($entityType);
            $this->registry[$entityType] = $metadata->getEntityConnection()->describeTable($metadata->getEntityTable());
            if ($metadata->getLinkField() != $metadata->getIdentifierField()) {
                unset($this->registry[$entityType][$metadata->getLinkField()]);
            }
            foreach ($this->providers as $providerClass) {
                if (isset($this->providers[$entityType])) {
                    $provider = $this->objectManager->get($providerClass);
                } elseif (isset($this->providers['default'])) {
                    $provider = $this->objectManager->get($providerClass);
                } else {
                    continue;
                }
                $this->registry[$entityType] = array_merge(
                    $this->registry[$entityType],
                    $provider->getAttributes($entityType)
                );
            }
        }
        return $this->registry[$entityType];
    }
}
