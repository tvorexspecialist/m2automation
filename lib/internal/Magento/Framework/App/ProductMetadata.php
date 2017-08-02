<?php
/**
 * Magento application product metadata
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\Composer\ComposerFactory;
use \Magento\Framework\Composer\ComposerJsonFinder;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Composer\ComposerInformation;

/**
 * Class ProductMetadata
 * @package Magento\Framework\App
 * @since 2.0.0
 */
class ProductMetadata implements ProductMetadataInterface
{
    /**
     * Magento product edition
     */
    const EDITION_NAME  = 'Community';

    /**
     * Magento product name
     */
    const PRODUCT_NAME  = 'Magento';

    /**
     * Product version
     *
     * @var string
     * @since 2.1.0
     */
    protected $version;

    /**
     * @var \Magento\Framework\Composer\ComposerJsonFinder
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    protected $composerJsonFinder;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation
     * @since 2.1.0
     */
    private $composerInformation;

    /**
     * @param ComposerJsonFinder $composerJsonFinder
     * @since 2.1.0
     */
    public function __construct(ComposerJsonFinder $composerJsonFinder)
    {
        $this->composerJsonFinder = $composerJsonFinder;
    }

    /**
     * Get Product version
     *
     * @return string
     * @since 2.0.0
     */
    public function getVersion()
    {
        if (!$this->version) {
            if (!($this->version = $this->getSystemPackageVersion())) {
                if ($this->getComposerInformation()->isMagentoRoot()) {
                    $this->version = $this->getComposerInformation()->getRootPackage()->getPrettyVersion();
                } else {
                    $this->version = 'UNKNOWN';
                }
            }
        }
        return $this->version;
    }

    /**
     * Get Product edition
     *
     * @return string
     * @since 2.0.0
     */
    public function getEdition()
    {
        return self::EDITION_NAME;
    }

    /**
     * Get Product name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return self::PRODUCT_NAME;
    }

    /**
     * Get version from system package
     *
     * @return string
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getSystemPackageVersion()
    {
        $packages = $this->getComposerInformation()->getSystemPackages();
        foreach ($packages as $package) {
            if (isset($package['name']) && isset($package['version'])) {
                return $package['version'];
            }
        }
        return '';
    }

    /**
     * Load composerInformation
     *
     * @return ComposerInformation
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getComposerInformation()
    {
        if (!$this->composerInformation) {
            $directoryList              = new DirectoryList(BP);
            $composerFactory            = new ComposerFactory($directoryList, $this->composerJsonFinder);
            $this->composerInformation  = new ComposerInformation($composerFactory);
        }
        return $this->composerInformation;
    }
}
