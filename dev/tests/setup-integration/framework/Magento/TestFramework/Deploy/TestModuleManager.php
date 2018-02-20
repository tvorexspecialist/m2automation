<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Deploy;

/**
 * The purpose of this class is adding test modules files to Magento code base.
 */
class TestModuleManager
{
    /**
     * Name of file of DB XML declaration.
     */
    const DECLARATIVE_FILE_NAME = "db_schema.xml";

    /**
     * Add test module files to Magento code base.
     *
     * @param  string $moduleName
     * @return void
     * @throws \RuntimeException
     */
    public function addModuleFiles($moduleName)
    {
        $moduleName = str_replace("Magento_", "", $moduleName);
        $pathToCommittedTestModules = TESTS_MODULES_PATH . '/Magento/' . $moduleName;
        $pathToInstalledMagentoInstanceModules = MAGENTO_MODULES_PATH . $moduleName;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $pathToCommittedTestModules,
                \RecursiveDirectoryIterator::FOLLOW_SYMLINKS
            )
        );
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $source = $file->getPathname();
                $relativePath = substr($source, strlen($pathToCommittedTestModules));
                $destination = $pathToInstalledMagentoInstanceModules . $relativePath;
                $targetDir = dirname($destination);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                copy($source, $destination);
            }
        }

        unset($iterator, $file);

        // Register the modules under '_files/'
        $pathPattern = $pathToInstalledMagentoInstanceModules . '/Test*/registration.php';
        $files = glob($pathPattern, GLOB_NOSORT);
        if ($files === false) {
            throw new \RuntimeException('glob() returned error while searching in \'' . $pathPattern . '\'');
        }
        foreach ($files as $file) {
            include $file;
        }
    }

    /**
     * Update module version.
     *
     * @param string $moduleName   Like Magento_TestSetupModule
     * @param string $revisionName Folder name, like reviisions/revision_1/db_schema.xml
     * @param string $fileName     For example db_schema.xml
     * @param string $fileDir      For example etc or Setup
     */
    public function updateRevision($moduleName, $revisionName, $fileName, $fileDir)
    {
        $modulePath = str_replace("Magento_", "", $moduleName);
        $folder = MAGENTO_MODULES_PATH . $modulePath;
        $oldFile = $folder . DIRECTORY_SEPARATOR . $fileDir . "/" . $fileName;
        $revisionFile = MAGENTO_MODULES_PATH . $modulePath . "/revisions/" .
            $revisionName . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($oldFile) && file_exists($revisionFile)) {
            unlink($oldFile);
            copy($revisionFile, $oldFile);
        } else {
            throw new \InvalidArgumentException("Old File or revision files paths are invalid");
        }
    }

    /**
     * Remove test module files to Magento code base.
     *
     * @param  string $moduleName
     * @return void
     */
    public function removeModuleFiles($moduleName)
    {
        $modulePath = str_replace("Magento_", "", $moduleName);
        $folder = MAGENTO_MODULES_PATH . $modulePath;

        //remove test modules from magento codebase
        if (is_dir($folder)) {
            \Magento\Framework\Filesystem\Io\File::rmdirRecursive($folder);
        }
    }

    /**
     * Update module files.
     *
     * @param  string $moduleName
     * @return void
     */
    public function updateModuleFiles($moduleName)
    {
        $pathToCommittedTestModules = TESTS_MODULES_PATH . '/UpgradeScripts/' . $moduleName;
        $pathToInstalledMagentoInstanceModules = MAGENTO_MODULES_PATH . $moduleName;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $pathToCommittedTestModules,
                \RecursiveDirectoryIterator::FOLLOW_SYMLINKS
            )
        );
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $source = $file->getPathname();
                $relativePath = substr($source, strlen($pathToCommittedTestModules));
                $destination = $pathToInstalledMagentoInstanceModules . $relativePath;
                $targetDir = dirname($destination);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                copy($source, $destination);
            }
        }
    }
}
