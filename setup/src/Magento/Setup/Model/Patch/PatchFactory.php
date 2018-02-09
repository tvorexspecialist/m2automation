<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Patch;

use Magento\Framework\ObjectManagerInterface;

/**
 * This factory allows to create data patches:
 * @see PatchInterface
 */
class PatchFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }
    /**
     * Create new instance of
     * @param string $instanceName
     * @return PatchInterface
     */
    public function create($instanceName)
    {
        $patchInstance = $this->objectManager->create('\\' . $instanceName, []);
        if (!$patchInstance instanceof PatchInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    "%s should implement %s interface",
                    $instanceName,
                    PatchInterface::class
                )
            );
        }

        return $patchInstance;
    }
}
