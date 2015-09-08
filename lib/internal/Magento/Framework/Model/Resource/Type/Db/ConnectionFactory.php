<?php
/**
 * Connection adapter factory
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Resource\Type\Db;

use Magento\Framework\ObjectManagerInterface;

class ConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $connectionConfig)
    {
        /** @var \Magento\Framework\App\Resource\ConnectionAdapterInterface $adapterInstance */
        $adapterInstance = $this->objectManager->create(
            \Magento\Framework\App\Resource\ConnectionAdapterInterface::class,
            ['config' => $connectionConfig]
        );

        return $adapterInstance->getConnection($this->objectManager->get(\Magento\Framework\DB\LoggerInterface::class));
    }
}
