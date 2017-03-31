<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Service;

use Magento\RequireJs\Model\FileManagerFactory;
use Magento\Framework\View\DesignInterfaceFactory;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\Asset\RepositoryFactory;
use Magento\Framework\RequireJs\ConfigFactory;

/**
 * Deploy RequireJS configuration
 *
 * @api
 */
class DeployRequireJsConfig
{
    /**
     * Default jobs amount
     */
    const DEFAULT_JOBS_AMOUNT = 4;

    /**
     * @var ListInterface
     */
    private $themeList;

    /**
     * @var DesignInterfaceFactory
     */
    private $designFactory;

    /**
     * @var RepositoryFactory
     */
    private $assetRepoFactory;

    /**
     * @var FileManagerFactory
     */
    private $fileManagerFactory;

    /**
     * @var ConfigFactory
     */
    private $requireJsConfigFactory;

    /**
     * DeployRequireJsConfig constructor
     *
     * @param ListInterface $themeList
     * @param DesignInterfaceFactory $designFactory
     * @param RepositoryFactory $assetRepoFactory
     * @param FileManagerFactory $fileManagerFactory
     * @param ConfigFactory $requireJsConfigFactory
     */
    public function __construct(
        ListInterface $themeList,
        DesignInterfaceFactory $designFactory,
        RepositoryFactory $assetRepoFactory,
        FileManagerFactory $fileManagerFactory,
        ConfigFactory $requireJsConfigFactory
    ) {
        $this->themeList = $themeList;
        $this->designFactory = $designFactory;
        $this->assetRepoFactory = $assetRepoFactory;
        $this->fileManagerFactory = $fileManagerFactory;
        $this->requireJsConfigFactory = $requireJsConfigFactory;
    }

    /**
     * @param string $areaCode
     * @param string $themePath
     * @return bool true on success
     */
    public function deploy($areaCode, $themePath)
    {
        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $theme = $this->themeList->getThemeByFullPath($areaCode . '/' . $themePath);
        $design = $this->designFactory->create()->setDesignTheme($theme, $areaCode);

        $assetRepo = $this->assetRepoFactory->create(['design' => $design]);
        /** @var \Magento\RequireJs\Model\FileManager $fileManager */
        $fileManager = $this->fileManagerFactory->create(
            [
                'config' => $this->requireJsConfigFactory->create(
                    [
                        'assetRepo' => $assetRepo,
                        'design' => $design,
                    ]
                ),
                'assetRepo' => $assetRepo,
            ]
        );

        $fileManager->createRequireJsConfigAsset();

        $fileManager->createMinResolverAsset();

        return true;
    }
}
