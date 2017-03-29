<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Source;

use Magento\Framework\App\Utility\Files;
use Magento\Deploy\Package\PackageFileFactory;

/**
 * Class Modules
 *
 * Provides files collected from all modules
 *
 * @api
 */
class Modules implements SourceInterface
{
    const TYPE = 'modules';

    /**
     * @var Files
     */
    private $filesUtil;

    /**
     * @var PackageFileFactory
     */
    private $packageFileFactory;

    /**
     * Modules constructor
     *
     * @param Files $filesUtil
     * @param PackageFileFactory $packageFileFactory
     */
    public function __construct(
        Files $filesUtil,
        PackageFileFactory $packageFileFactory
    ) {
        $this->filesUtil = $filesUtil;
        $this->packageFileFactory = $packageFileFactory;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $files = [];
        foreach ($this->filesUtil->getStaticPreProcessingFiles() as $info) {
            list($area, $theme, $locale, $module, $fileName, $fullPath) = $info;
            if (!empty($module) && empty($theme)) {
                $locale = $locale ?: null;
                $params = [
                    'area' => $area,
                    'theme' => null,
                    'locale' => $locale,
                    'module' => $module,
                    'fileName' => $fileName,
                    'sourcePath' => $fullPath
                ];
                $files[] = $this->packageFileFactory->create($params);
            }
        }
        return $files;
    }
}
