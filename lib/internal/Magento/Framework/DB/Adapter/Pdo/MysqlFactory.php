<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Adapter\Pdo;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for Mysql adapter
 * @since 2.2.0
 */
class MysqlFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.2.0
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
     * @param array $config
     * @param LoggerInterface|null $logger
     * @param SelectFactory|null $selectFactory
     * @return Mysql
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    public function create(
        $className,
        array $config,
        LoggerInterface $logger = null,
        SelectFactory $selectFactory = null
    ) {
        if (!in_array(Mysql::class, class_parents($className, true) + [$className => $className])) {
            throw new \InvalidArgumentException('Invalid class, ' . $className . ' must extend ' . Mysql::class . '.');
        }
        $arguments = [
            'config' => $config
        ];
        if ($logger) {
            $arguments['logger'] = $logger;
        }
        if ($selectFactory) {
            $arguments['selectFactory'] = $selectFactory;
        }
        return $this->objectManager->create(
            $className,
            $arguments
        );
    }
}
