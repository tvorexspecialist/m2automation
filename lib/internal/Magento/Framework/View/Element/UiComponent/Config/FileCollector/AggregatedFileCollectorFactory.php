<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config\FileCollector;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class AggregatedFileCollectorFactory
 * @since 2.0.0
 */
class AggregatedFileCollectorFactory
{
    const INSTANCE_NAME =
        \Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollector::class;

    /**
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
     * Create config reader
     *
     * @param array $arguments
     * @return AggregatedFileCollector
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create(static::INSTANCE_NAME, $arguments);
    }
}
