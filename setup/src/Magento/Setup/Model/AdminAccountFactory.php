<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Setup\Module\Setup;
use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class \Magento\Setup\Model\AdminAccountFactory
 *
 * @since 2.0.0
 */
class AdminAccountFactory
{
    /**
     * @var ServiceLocatorInterface
     * @since 2.0.0
     */
    protected $serviceLocator;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @since 2.0.0
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param AdapterInterface $connection
     * @param array $data
     * @return AdminAccount
     * @since 2.0.0
     */
    public function create(AdapterInterface $connection, $data)
    {
        return new AdminAccount(
            $connection,
            $this->serviceLocator->get(\Magento\Framework\Encryption\Encryptor::class),
            $data
        );
    }
}
