<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Model\PackagesData;

/**
 * Extension Grid
 */
class Extension
{
    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * @var TypeMapper
     */
    private $typeMapper;

    /**
     * @var PackagesData
     */
    private $packagesData;

    /**
     * @param ComposerInformation $composerInformation
     * @param PackagesData $packagesData
     * @param TypeMapper $typeMapper
     */
    public function __construct(
        ComposerInformation $composerInformation,
        PackagesData $packagesData,
        TypeMapper $typeMapper
    ) {
        $this->composerInformation = $composerInformation;
        $this->packagesData = $packagesData;
        $this->typeMapper = $typeMapper;
    }

    /**
     * Get formatted list of installed extensions
     *
     * @return array
     */
    public function getList()
    {
        $extensions = $this->packagesData->getInstalledPackages();
        $packagesForUpdate = $this->packagesData->getPackagesForUpdate();

        foreach ($extensions as &$extension) {
            $extension['update'] = array_key_exists($extension['name'], $packagesForUpdate);
            $extension['uninstall'] = true;
            if (
                $extension['type'] === ComposerInformation::METAPACKAGE_PACKAGE_TYPE
                || !$this->composerInformation->isPackageInComposerJson($extension['name'])
            ) {
                $extension['uninstall'] = false;
            }
        }

        return $this->formatExtensions($extensions);
    }

    /**
     * Get formatted list of extensions that have new version
     *
     * @return array
     */
    public function getListForUpdate()
    {
        $extensions = $this->packagesData->getPackagesForUpdate();

        return $this->formatExtensions($extensions);
    }

    /**
     * Format given array of extensions, add vendor and format extension type
     *
     * @param array $extensions
     * @return array
     */
    private function formatExtensions(array $extensions)
    {
        foreach ($extensions as &$extension) {
            $extension['vendor'] = current(explode('/', $extension['name']));
            $extension['type'] = $this->typeMapper->map($extension['name'], $extension['type']);
        }
        return array_values($extensions);
    }
}
