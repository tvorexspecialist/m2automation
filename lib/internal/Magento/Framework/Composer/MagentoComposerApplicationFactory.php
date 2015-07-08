<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Magento\Composer\MagentoComposerApplication;
use Magento\Framework\App\Filesystem\DirectoryList;

class MagentoComposerApplicationFactory
{

    /**
     * @var string
     */
    private $pathToComposerHome;

    /**
     * @var string
     */
    private $pathToComposerJson;

    /**
     * Constructor
     *
     * @param ComposerJsonFinder $composerJsonFinder
     * @param DirectoryList $directoryList
     */
    public function __construct(ComposerJsonFinder $composerJsonFinder, DirectoryList $directoryList)
    {
        $this->pathToComposerJson = $composerJsonFinder->findComposerJson();
        $this->pathToComposerHome = $directoryList->getPath(DirectoryList::COMPOSER_HOME);
    }

    /**
     * Creates MagentoComposerApplication instance
     *
     * @return MagentoComposerApplication
     */
    public function create()
    {
        return new MagentoComposerApplication($this->pathToComposerHome, $this->pathToComposerJson);
    }
}
