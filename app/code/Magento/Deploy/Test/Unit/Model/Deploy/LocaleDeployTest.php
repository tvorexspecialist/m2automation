<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\Deploy;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LocaleDeployTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Deploy\Model\Deploy\LocaleDeploy
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Translate\Js\Config
     */
    private $jsTranslationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\Minification
     */
    private $minificationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\RepositoryFactory
     */
    private $assetRepoFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\RequireJs\Model\FileManagerFactory
     */
    private $fileManagerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\RequireJs\ConfigFactory
     */
    private $configFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\Bundle\Manager
     */
    private $bundleManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Utility\Files
     */
    private $filesUtilMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\DesignInterfaceFactory
     */
    private $designFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolverMock;

    protected function setUp()
    {
        $outputMock = $this->getMock(\Symfony\Component\Console\Output\OutputInterface::class, [], [], '', false);
        $loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class, [], [], '', false);
        $this->filesUtilMock = $this->getMock(\Magento\Framework\App\Utility\Files::class, [], [], '', false);
        $assetRepoMock = $this->getMock(\Magento\Framework\View\Asset\Repository::class, [], [], '', false);
        $this->minificationMock = $this->getMock(\Magento\Framework\View\Asset\Minification::class, [], [], '', false);
        $this->jsTranslationMock = $this->getMock(\Magento\Framework\Translate\Js\Config::class, [], [], '', false);
        $assetPublisherMock = $this->getMock(\Magento\Framework\App\View\Asset\Publisher::class, [], [], '', false);
        $this->assetRepoFactoryMock = $this->getMock(
            \Magento\Framework\View\Asset\RepositoryFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->fileManagerFactoryMock = $this->getMock(
            \Magento\RequireJs\Model\FileManagerFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->configFactoryMock = $this->getMock(
            \Magento\Framework\RequireJs\ConfigFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->bundleManagerMock = $this->getMock(
            \Magento\Framework\View\Asset\Bundle\Manager::class,
            [],
            [],
            '',
            false
        );
        $themeProviderMock = $this->getMock(
            \Magento\Framework\View\Design\Theme\ThemeProviderInterface::class,
            [],
            [],
            '',
            false
        );
        $this->designFactoryMock = $this->getMock(
            \Magento\Framework\View\DesignInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->localeResolverMock = $this->getMock(
            \Magento\Framework\Locale\ResolverInterface::class,
            [],
            [],
            '',
            false
        );

        $this->model = new \Magento\Deploy\Model\Deploy\LocaleDeploy(
            $outputMock,
            $this->jsTranslationMock,
            $this->minificationMock,
            $assetRepoMock,
            $this->assetRepoFactoryMock,
            $this->fileManagerFactoryMock,
            $this->configFactoryMock,
            $assetPublisherMock,
            $this->bundleManagerMock,
            $themeProviderMock,
            $loggerMock,
            $this->filesUtilMock,
            $this->designFactoryMock,
            $this->localeResolverMock,
            []
        );
    }

    public function testDeploy()
    {
        $area = 'adminhtml';
        $themePath = '/theme/path';
        $locale = 'en_US';

        $designMock = $this->getMock(\Magento\Framework\View\DesignInterface::class, [], [], '', false);
        $assetRepoMock = $this->getMock(\Magento\Framework\View\Asset\Repository::class, [], [], '', false);
        $requireJsConfigMock = $this->getMock(\Magento\Framework\RequireJs\Config::class, [], [], '', false);
        $fileManagerMock = $this->getMock(\Magento\RequireJs\Model\FileManager::class, [], [], '', false);

        $this->model->setOptions([\Magento\Deploy\Console\Command\DeployStaticOptionsInterface::NO_JAVASCRIPT => 0]);

        $this->localeResolverMock->expects($this->once())->method('setLocale')->with($locale);
        $this->designFactoryMock->expects($this->once())->method('create')->willReturn($designMock);
        $designMock->expects($this->once())->method('setDesignTheme')->with($themePath, $area)->willReturnSelf();
        $this->assetRepoFactoryMock->expects($this->once())->method('create')->with(['design' => $designMock])
            ->willReturn($assetRepoMock);
        $this->configFactoryMock->expects($this->once())->method('create')->willReturn($requireJsConfigMock);
        $this->fileManagerFactoryMock->expects($this->once())->method('create')->willReturn($fileManagerMock);

        $fileManagerMock->expects($this->once())->method('createRequireJsConfigAsset')->willReturnSelf();
        $this->filesUtilMock->expects($this->once())->method('getStaticPreProcessingFiles')->willReturn([]);
        $this->filesUtilMock->expects($this->once())->method('getStaticLibraryFiles')->willReturn([]);

        $this->jsTranslationMock->expects($this->once())->method('dictionaryEnabled')->willReturn(false);
        $this->minificationMock->expects($this->once())->method('isEnabled')->with('js')->willReturn(true);
        $fileManagerMock->expects($this->once())->method('createMinResolverAsset')->willReturnSelf();

        $this->bundleManagerMock->expects($this->once())->method('flush');

        $this->assertEquals(
            \Magento\Framework\Console\Cli::RETURN_SUCCESS,
            $this->model->deploy($area, $themePath, $locale)
        );
    }
}
