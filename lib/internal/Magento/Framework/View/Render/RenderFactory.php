<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Render;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\RenderInterface;

/**
 * Class RenderFactory
 *
 * @api
 * @since 2.0.0
 */
class RenderFactory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get method
     *
     * @param string $type
     * @return RenderInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function get($type)
    {
        $className = 'Magento\\Framework\\View\\Render\\' . ucfirst($type);
        $model = $this->objectManager->get($className);
        if (!$model instanceof RenderInterface) {
            throw new \InvalidArgumentException(
                'Type "' . $type . '" is not instance on Magento\Framework\View\RenderInterface'
            );
        }
        return $model;
    }
}
