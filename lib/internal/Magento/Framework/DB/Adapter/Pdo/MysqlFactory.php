<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Adapter\Pdo;

use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Factory for Mysql adapter
 */
class MysqlFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create instance of Mysql adapter
     *
     * @param string $className
     * @param LoggerInterface $logger
     * @param array $config
     * @param SelectFactory|null $selectFactory
     * @return Mysql
     * @throws \InvalidArgumentException
     */
    public function create(
        $className,
        LoggerInterface $logger,
        array $config,
        SelectFactory $selectFactory = null
    ) {
        if (!in_array(Mysql::class, class_parents($className, true) + [$className => $className])) {
            throw new \InvalidArgumentException('Invalid class, ' . $className . ' must extend ' . Mysql::class . '.');
        }
        $arguments = [
            'logger' => $logger,
            'config' => $config
        ];
        if  ($selectFactory) {
            $arguments['selectFactory'] = $selectFactory;
        }
        return $this->objectManager->create(
            $className,
            $arguments
        );
    }
}
