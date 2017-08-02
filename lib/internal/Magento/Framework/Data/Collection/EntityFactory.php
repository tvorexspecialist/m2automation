<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Collection;

/**
 * Class \Magento\Framework\Data\Collection\EntityFactory
 *
 * @since 2.0.0
 */
class EntityFactory implements EntityFactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $className
     * @param array $data
     * @throws \LogicException
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function create($className, array $data = [])
    {
        $model = $this->_objectManager->create($className, $data);
        //TODO: fix that when this factory used only for \Magento\Framework\Model\AbstractModel
        //if (!$model instanceof \Magento\Framework\Model\AbstractModel) {
        //    throw new \LogicException($className . ' doesn\'t implement \Magento\Framework\Model\AbstractModel');
        //}
        return $model;
    }
}
