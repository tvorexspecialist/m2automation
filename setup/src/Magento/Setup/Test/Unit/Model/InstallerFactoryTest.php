<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\App\ObjectManager;
use \Magento\Setup\Model\InstallerFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallerFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProviderMock;

    public function testCreate()
    {
        $this->objectManagerProviderMock = $this->getMockBuilder(\Magento\Setup\Model\ObjectManagerProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->with(\Magento\Setup\Model\DeclarationInstaller::class)
            ->willReturn($this->createMock(\Magento\Setup\Model\DeclarationInstaller::class));
        $this->objectManagerProviderMock->expects($this->any())
            ->method('get')
            ->willReturn($objectManagerMock);
        $serviceLocatorMock = $this->getMockForAbstractClass(
            \Zend\ServiceManager\ServiceLocatorInterface::class,
            ['get']
        );
        $serviceLocatorMock->expects($this->any())->method('get')
            ->will($this->returnValueMap($this->getReturnValueMap()));

        $log = $this->getMockForAbstractClass(\Magento\Framework\Setup\LoggerInterface::class);
        $resourceFactoryMock = $this->createMock(\Magento\Setup\Module\ResourceFactory::class);
        $resourceFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->createMock(\Magento\Framework\App\ResourceConnection::class)));
        $installerFactory = new InstallerFactory($serviceLocatorMock, $resourceFactoryMock);
        $installer = $installerFactory->create($log);
        $this->assertInstanceOf(\Magento\Setup\Model\Installer::class, $installer);
    }

    /**
     * @return array
     */
    private function getReturnValueMap()
    {
        return [
            [
                \Magento\Framework\Setup\FilePermissions::class,
                $this->createMock(\Magento\Framework\Setup\FilePermissions::class),
            ],
            [
                \Magento\Framework\App\DeploymentConfig\Writer::class,
                $this->createMock(\Magento\Framework\App\DeploymentConfig\Writer::class),
            ],
            [
                \Magento\Framework\App\DeploymentConfig\Reader::class,
                $this->createMock(\Magento\Framework\App\DeploymentConfig\Reader::class),
            ],
            [
                \Magento\Framework\App\DeploymentConfig::class,
                $this->createMock(\Magento\Framework\App\DeploymentConfig::class),
            ],
            [
                \Magento\Framework\Module\ModuleList::class,
                $this->createMock(\Magento\Framework\Module\ModuleList::class),
            ],
            [
                \Magento\Framework\Module\ModuleList\Loader::class,
                $this->createMock(\Magento\Framework\Module\ModuleList\Loader::class),
            ],
            [
                \Magento\Setup\Model\AdminAccountFactory::class,
                $this->createMock(\Magento\Setup\Model\AdminAccountFactory::class),
            ],
            [
                \Magento\Setup\Module\ConnectionFactory::class,
                $this->createMock(\Magento\Setup\Module\ConnectionFactory::class),
            ],
            [
                \Magento\Framework\App\MaintenanceMode::class,
                $this->createMock(\Magento\Framework\App\MaintenanceMode::class),
            ],
            [
                \Magento\Framework\Filesystem::class,
                $this->createMock(\Magento\Framework\Filesystem::class),
            ],
            [
                \Magento\Setup\Model\ObjectManagerProvider::class,
                $this->objectManagerProviderMock
            ],
            [
                \Magento\Framework\Model\ResourceModel\Db\TransactionManager::class,
                $this->createMock(\Magento\Framework\Model\ResourceModel\Db\TransactionManager::class),
            ],
            [
                \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class,
                $this->createMock(\Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class),
            ],
            [
                \Magento\Setup\Model\ConfigModel::class,
                $this->createMock(\Magento\Setup\Model\ConfigModel::class),
            ],
            [
                \Magento\Framework\App\State\CleanupFiles::class,
                $this->createMock(\Magento\Framework\App\State\CleanupFiles::class),
            ],
            [
                \Magento\Setup\Validator\DbValidator::class,
                $this->createMock(\Magento\Setup\Validator\DbValidator::class),
            ],
            [
                \Magento\Setup\Module\SetupFactory::class,
                $this->createMock(\Magento\Setup\Module\SetupFactory::class),
            ],
            [
                \Magento\Setup\Module\DataSetupFactory::class,
                $this->createMock(\Magento\Setup\Module\DataSetupFactory::class),
            ],
            [
                \Magento\Framework\Setup\SampleData\State::class,
                $this->createMock(\Magento\Framework\Setup\SampleData\State::class),
            ],
            [
                \Magento\Setup\Model\PhpReadinessCheck::class,
                $this->createMock(\Magento\Setup\Model\PhpReadinessCheck::class),
            ],
        ];
    }
}
