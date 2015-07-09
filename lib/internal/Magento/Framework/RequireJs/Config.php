<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\RequireJs;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\Minification;

/**
 * Provider of RequireJs config information
 */
class Config
{
    /**
     * Name of sub-directory where generated RequireJs config is placed
     */
    const DIR_NAME = '_requirejs';

    /**
     * File name of RequireJs config
     */
    const CONFIG_FILE_NAME = 'requirejs-config.js';

    /**
     * File name of RequireJs
     */
    const REQUIRE_JS_FILE_NAME = 'requirejs/require.js';

    /**
     * File name of StaticJs
     */
    const STATIC_FILE_NAME = 'mage\requirejs\static.js';

    /**
     * File name of StaticJs
     */
    const BUNDLE_JS_DIR = 'js/bundle';

    /**
     * Template for combined RequireJs config file
     */
    const FULL_CONFIG_TEMPLATE = <<<config
(function(require){
%base%
%function%

%usages%
})(require);
config;

    /**
     * Template for wrapped partial config
     */
    const PARTIAL_CONFIG_TEMPLATE = <<<config
(function() {
%config%
require.config(config);
})();

config;

    /**
     * @var \Magento\Framework\RequireJs\Config\File\Collector\Aggregated
     */
    private $fileSource;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $design;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $baseDir;

    /**
     * @var \Magento\Framework\View\Asset\ContextInterface
     */
    private $staticContext;

    /**
     * @var \Magento\Framework\Code\Minifier\AdapterInterface
     */
    private $minifyAdapter;

    /**
     * @var Minification
     */
    private $minification;

    /**
     * @param \Magento\Framework\RequireJs\Config\File\Collector\Aggregated $fileSource
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Filesystem $appFilesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Code\Minifier\AdapterInterface $minifyAdapter
     * @param Minification $minification
     */
    public function __construct(
        \Magento\Framework\RequireJs\Config\File\Collector\Aggregated $fileSource,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Filesystem $appFilesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Code\Minifier\AdapterInterface $minifyAdapter,
        Minification $minification
    ) {
        $this->fileSource = $fileSource;
        $this->design = $design;
        $this->baseDir = $appFilesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->staticContext = $assetRepo->getStaticViewFileContext();
        $this->minifyAdapter = $minifyAdapter;
        $this->minification = $minification;
    }

    /**
     * Get aggregated distributed configuration
     *
     * @return string
     */
    public function getConfig()
    {
        $distributedConfig = '';
        $baseConfig = $this->getBaseConfig();
        $customConfigFiles = $this->fileSource->getFiles($this->design->getDesignTheme(), self::CONFIG_FILE_NAME);
        foreach ($customConfigFiles as $file) {
            $config = $this->baseDir->readFile($this->baseDir->getRelativePath($file->getFilename()));
            $distributedConfig .= str_replace(
                ['%config%', '%context%'],
                [$config, $file->getModule()],
                self::PARTIAL_CONFIG_TEMPLATE
            );
        }

        $fullConfig = str_replace(
            ['%function%', '%base%', '%usages%'],
            [$distributedConfig, $baseConfig],
            self::FULL_CONFIG_TEMPLATE
        );

        if ($this->minification->isEnabled('js')) {
            $fullConfig = $this->minifyAdapter->minify($fullConfig);
        }

        return $fullConfig;
    }

    /**
     * Get path to config file relative to directory, where all config files with different context are located
     *
     * @return string
     */
    public function getConfigFileRelativePath()
    {
        return self::DIR_NAME . '/' . $this->staticContext->getConfigPath() . '/' . $this->getConfigFileName();
    }

    /**
     * Get path to config file relative to directory, where all config files with different context are located
     *
     * @return string
     */
    public function getRequireJsFileRelativePath()
    {
        return $this->staticContext->getConfigPath() . '/' .self::REQUIRE_JS_FILE_NAME;
    }

    /**
     * Get base RequireJs configuration necessary for working with Magento application
     *
     * @return string
     */
    public function getBaseConfig()
    {
        $result = '';
        $config = [
            'baseUrl' => $this->staticContext->getBaseUrl() . $this->staticContext->getPath(),
        ];
        $config = json_encode($config, JSON_UNESCAPED_SLASHES);
        if ($this->minification->isEnabled(('js'))) {
            $result .= $this->getMinificationCode();
        }
        $result .= "require.config($config);";
        return $result;
    }

    /**
     * @return string
     */
    protected function getConfigFileName()
    {
        return $this->minification->addMinifiedSign(self::CONFIG_FILE_NAME);
    }

    /**
     * @return string
     */
    protected function getMinificationCode()
    {
        return <<<code
    if (!require.s.contexts._._nameToUrl) {
        require.s.contexts._._nameToUrl = require.s.contexts._.nameToUrl;
        require.s.contexts._.nameToUrl = function (moduleName, ext, skipExt) {
            if (!ext && !skipExt) {
                ext = '.min.js';
            }
            return require.s.contexts._._nameToUrl.apply(require.s.contexts._, [moduleName, ext, skipExt])
                .replace(/(\.min\.min\.js)(\?.*)*$/, '.min.js$2');
        }
    }

code;
    }
}
