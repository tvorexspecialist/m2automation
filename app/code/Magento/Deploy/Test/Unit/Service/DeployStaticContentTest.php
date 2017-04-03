<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Process\Queue;
use Magento\Deploy\Service\Bundle;
use Magento\Deploy\Service\DeployPackage;
use Magento\Deploy\Service\DeployRequireJsConfig;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Deploy\Process\QueueFactory;
use Magento\Deploy\Service\DeployTranslationsDictionary;
use Magento\Deploy\Service\MinifyTemplates;
use Magento\Deploy\Strategy\CompactDeploy;
use Magento\Deploy\Strategy\DeployStrategyFactory;

use Magento\Framework\App\View\Deployment\Version\StorageInterface;
use Magento\Framework\ObjectManagerInterface;

use Psr\Log\LoggerInterface;

use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Static Content deploy service class unit tests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeployStaticContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeployStaticContent|Mock
     */
    private $service;

    /**
     * @var DeployStrategyFactory|Mock
     */
    private $deployStrategyFactory;

    /**
     * @var QueueFactory|Mock
     */
    private $queueFactory;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    /**
     * @var ObjectManagerInterface|Mock
     */
    private $objectManager;

    /**
     * @var StorageInterface|Mock
     */
    private $versionStorage;

    protected function setUp()
    {
        $this->deployStrategyFactory = $this->getMock(
            DeployStrategyFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->queueFactory = $this->getMock(
            QueueFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->logger = $this->getMockForAbstractClass(
            LoggerInterface::class,
            [],
            '',
            false
        );
        $this->objectManager = $this->getMock(
            ObjectManagerInterface::class,
            ['create', 'get', 'configure'],
            [],
            '',
            false
        );
        $this->versionStorage = $this->getMockForAbstractClass(
            StorageInterface::class,
            ['save'],
            '',
            false
        );
        $this->versionStorage->expects($this->once())->method('save');

        $this->service = new DeployStaticContent(
            $this->objectManager,
            $this->logger,
            $this->versionStorage,
            $this->deployStrategyFactory,
            $this->queueFactory
        );
    }

    public function testDeploy()
    {
        $options = [
            'strategy' =>  'compact',
            'no-javascript' => false,
            'no-html-minify' => false
        ];

        $package = $this->getMock(Package::class, [], [], '', false);
        $package->expects($this->exactly(1))->method('isVirtual')->willReturn(false);
        $package->expects($this->exactly(3))->method('getArea')->willReturn('area');
        $package->expects($this->exactly(3))->method('getTheme')->willReturn('theme');
        $package->expects($this->exactly(2))->method('getLocale')->willReturn('locale');

        $packages = [
            'package' => $package
        ];

        $this->versionStorage->expects($this->once())->method('save');

        $queue = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->queueFactory->expects($this->once())->method('create')->willReturn($queue);

        $strategy = $this->getMockBuilder(CompactDeploy::class)
            ->setMethods(['deploy'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $strategy->expects($this->once())->method('deploy')
            ->with($options)
            ->willReturn($packages);
        $this->deployStrategyFactory->expects($this->once())
            ->method('create')
            ->with('compact', ['queue' => $queue])
            ->willReturn($strategy);

        $deployPackageService = $this->getMockBuilder(DeployPackage::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $deployRjsConfig = $this->getMockBuilder(DeployRequireJsConfig::class)
            ->setMethods(['deploy'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $deployI18n = $this->getMockBuilder(DeployTranslationsDictionary::class)
            ->setMethods(['deploy'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $deployBundle = $this->getMockBuilder(Bundle::class)
            ->setMethods(['deploy'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $minifyTemplates = $this->getMockBuilder(MinifyTemplates::class)
            ->setMethods(['minifyTemplates'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManager->expects($this->exactly(4))
            ->method('create')
            ->withConsecutive(
                [DeployPackage::class, ['logger' => $this->logger]],
                [DeployRequireJsConfig::class, ['logger' => $this->logger]],
                [DeployTranslationsDictionary::class, ['logger' => $this->logger]],
                [Bundle::class, ['logger' => $this->logger]]
            )
            ->willReturnOnConsecutiveCalls(
                $deployPackageService,
                $deployRjsConfig,
                $deployI18n,
                $deployBundle
            );

        $this->objectManager->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive(
                [MinifyTemplates::class]
            )
            ->willReturnOnConsecutiveCalls(
                $minifyTemplates
            );

        $this->assertEquals(
            null,
            $this->service->deploy($options)
        );
    }
}
