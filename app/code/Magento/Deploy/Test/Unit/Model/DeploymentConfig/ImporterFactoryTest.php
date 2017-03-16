<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\ImporterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;

class ImporterFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var ImporterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $importerFactory;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->importerFactory = new ImporterFactory($this->objectManagerMock);
    }

    public function testCreate()
    {
        $className = 'some/class/name';

        /** @var ImporterInterface|\PHPUnit_Framework_MockObject_MockObject $importerMock */
        $importerMock = $this->getMockBuilder(ImporterInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, [])
            ->willReturn($importerMock);

        $this->assertSame($importerMock, $this->importerFactory->create($className));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage Type "some/class/name" is not instance on Magento\Framework\App\DeploymentConfig\ImporterInterface
     * @codingStandardsIgnoreEnd
     */
    public function testCreateWithInvalidArgumentException()
    {
        $className = 'some/class/name';

        /** @var \StdClass|\PHPUnit_Framework_MockObject_MockObject $importerMock */
        $importerMock = $this->getMockBuilder(\StdClass::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, [])
            ->willReturn($importerMock);

        $this->importerFactory->create($className);
    }
}
