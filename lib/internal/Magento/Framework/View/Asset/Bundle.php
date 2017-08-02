<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Bundle\Manager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\File\FallbackContext;

/**
 * Bundle model
 * @deprecated 2.2.0 since 2.2.0
 * @see \Magento\Deploy\Package\Bundle
 * @since 2.0.0
 */
class Bundle
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $assets = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $assetsContent = [];

    /**
     * @var \Magento\Framework\View\Asset\Bundle\ConfigInterface
     * @since 2.0.0
     */
    protected $bundleConfig;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $bundleNames = [
        Manager::ASSET_TYPE_JS => 'jsbuild',
        Manager::ASSET_TYPE_HTML => 'text'
    ];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $content = [];

    /**
     * @var Minification
     * @since 2.0.0
     */
    protected $minification;

    /**
     * @param Filesystem $filesystem
     * @param Bundle\ConfigInterface $bundleConfig
     * @param Minification $minification
     * @since 2.0.0
     */
    public function __construct(
        Filesystem $filesystem,
        Bundle\ConfigInterface $bundleConfig,
        Minification $minification
    ) {
        $this->filesystem = $filesystem;
        $this->bundleConfig = $bundleConfig;
        $this->minification = $minification;
    }

    /**
     * @param LocalInterface $asset
     * @return void
     * @since 2.0.0
     */
    public function addAsset(LocalInterface $asset)
    {
        $this->init($asset);
        $this->add($asset);
    }

    /**
     * Add asset into array
     *
     * @param LocalInterface $asset
     * @return void
     * @since 2.0.0
     */
    protected function add(LocalInterface $asset)
    {
        $partIndex = $this->getPartIndex($asset);
        $parts = &$this->assets[$this->getContextCode($asset)][$asset->getContentType()];
        if (!isset($parts[$partIndex])) {
            $parts[$partIndex]['assets'] = [];
        }
        $parts[$partIndex]['assets'][$this->getAssetKey($asset)] = $asset;
    }

    /**
     * @param LocalInterface $asset
     * @return void
     * @since 2.0.0
     */
    protected function init(LocalInterface $asset)
    {
        $contextCode = $this->getContextCode($asset);
        $type = $asset->getContentType();

        if (!isset($this->assets[$contextCode][$type])) {
            $this->assets[$contextCode][$type] = [];
        }
    }

    /**
     * @param LocalInterface $asset
     * @return string
     * @since 2.0.0
     */
    protected function getContextCode(LocalInterface $asset)
    {
        /** @var FallbackContext $context */
        $context = $asset->getContext();
        return $context->getAreaCode() . ':' . $context->getThemePath() . ':' . $context->getLocale();
    }

    /**
     * @param LocalInterface $asset
     * @return int
     * @since 2.0.0
     */
    protected function getPartIndex(LocalInterface $asset)
    {
        $parts = $this->assets[$this->getContextCode($asset)][$asset->getContentType()];

        $maxPartSize = $this->getMaxPartSize($asset);
        $minSpace = $maxPartSize;
        $minIndex = -1;
        if ($maxPartSize && count($parts)) {
            foreach ($parts as $partIndex => $part) {
                $space = $maxPartSize - $this->getSizePartWithNewAsset($asset, $part['assets']);
                if ($space >= 0 && $space < $minSpace) {
                    $minSpace = $space;
                    $minIndex = $partIndex;
                }
            }
        }

        return ($maxPartSize != 0) ? ($minIndex >= 0) ? $minIndex : count($parts) : 0;
    }

    /**
     * @param LocalInterface $asset
     * @return int
     * @since 2.0.0
     */
    protected function getMaxPartSize(LocalInterface $asset)
    {
        return $this->bundleConfig->getPartSize($asset->getContext());
    }

    /**
     * Get part size after adding new asset
     *
     * @param LocalInterface $asset
     * @param LocalInterface[] $assets
     * @return float
     * @since 2.0.0
     */
    protected function getSizePartWithNewAsset(LocalInterface $asset, $assets = [])
    {
        $assets[$this->getAssetKey($asset)] = $asset;
        return mb_strlen($this->getPartContent($assets), 'utf-8') / 1024;
    }

    /**
     * Build asset key
     *
     * @param LocalInterface $asset
     * @return string
     * @since 2.0.0
     */
    protected function getAssetKey(LocalInterface $asset)
    {
        $result = (($asset->getModule() == '') ? '' : $asset->getModule() . '/') . $asset->getFilePath();
        $result = $this->minification->addMinifiedSign($result);
        return $result;
    }

    /**
     * Prepare bundle for executing in js
     *
     * @param LocalInterface[] $assets
     * @return array
     * @since 2.0.0
     */
    protected function getPartContent($assets)
    {
        $contents = [];
        foreach ($assets as $key => $asset) {
            $contents[$key] = $this->getAssetContent($asset);
        }

        $partType = reset($assets)->getContentType();
        $content = json_encode($contents, JSON_UNESCAPED_SLASHES);
        $content = "require.config({\n" .
            "    config: {\n" .
            "        '" . $this->bundleNames[$partType] . "':" . $content . "\n" .
            "    }\n" .
            "});\n";

        return $content;
    }

    /**
     * Get content of asset
     *
     * @param LocalInterface $asset
     * @return string
     * @since 2.0.0
     */
    protected function getAssetContent(LocalInterface $asset)
    {
        $assetContextCode = $this->getContextCode($asset);
        $assetContentType = $asset->getContentType();
        $assetKey = $this->getAssetKey($asset);
        if (!isset($this->assetsContent[$assetContextCode][$assetContentType][$assetKey])) {
            $this->assetsContent[$assetContextCode][$assetContentType][$assetKey] = utf8_encode($asset->getContent());
        }

        return $this->assetsContent[$assetContextCode][$assetContentType][$assetKey];
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getInitJs()
    {
        return "require.config({\n" .
                "    bundles: {\n" .
                "        'mage/requirejs/static': [\n" .
                "            'jsbuild',\n" .
                "            'buildTools',\n" .
                "            'text',\n" .
                "            'statistician'\n" .
                "        ]\n" .
                "    },\n" .
                "    deps: [\n" .
                "        'jsbuild'\n" .
                "    ]\n" .
                "});\n";
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function flush()
    {
        foreach ($this->assets as $types) {
            $this->save($types);
        }
        $this->assets = [];
        $this->content = [];
        $this->assetsContent = [];
    }

    /**
     * @param array $types
     * @return void
     * @since 2.0.0
     */
    protected function save($types)
    {
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        $bundlePath = '';
        foreach ($types as $parts) {
            /** @var FallbackContext $context */
            $assetsParts = reset($parts);
            $context = reset($assetsParts['assets'])->getContext();
            $bundlePath = empty($bundlePath) ? $context->getPath() . Manager::BUNDLE_PATH : $bundlePath;
            $dir->delete($context->getPath() . DIRECTORY_SEPARATOR . Manager::BUNDLE_JS_DIR);
            $this->fillContent($parts, $context);
        }

        $this->content[max(0, count($this->content) - 1)] .= $this->getInitJs();

        foreach ($this->content as $partIndex => $content) {
            $dir->writeFile($this->minification->addMinifiedSign($bundlePath . $partIndex . '.js'), $content);
        }
    }

    /**
     * @param array $parts
     * @param FallbackContext $context
     * @return void
     * @since 2.0.0
     */
    protected function fillContent($parts, $context)
    {
        $index = count($this->content) > 0 ? count($this->content) - 1 : 0 ;
        foreach ($parts as $part) {
            if (!isset($this->content[$index])) {
                $this->content[$index] = '';
            } elseif ($this->bundleConfig->isSplit($context)) {
                ++$index;
                $this->content[$index] = '';
            }
            $this->content[$index] .= $this->getPartContent($part['assets']);
        }
    }
}
