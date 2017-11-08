<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type;

use Magento\Framework\ObjectManagerInterface;

/**
 * Create TypeHandler from its name
 */
class HandlerFactory
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
     * Instantiate type handler class
     *
     * @param string $typeClassName
     * @return HandlerInterface
     */
    public function create($typeClassName)
    {
        $typeHandlerClass = $this->objectManager->create($typeClassName);
        if (!($typeHandlerClass instanceof HandlerInterface)) {
            throw new \InvalidArgumentException(__('Invalid type name. Type handler does not exist.'));
        }

        return $typeHandlerClass;
    }
}
