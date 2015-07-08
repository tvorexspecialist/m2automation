<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Framework\View\Asset\ConfigInterface as AssetConfigInterface;

/**
 * A locally available static view file asset that can be referred with a file path
 *
 * This class is a value object with lazy loading of some of its data (content, physical file path)
 */
class File implements MergeableInterface
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var string|bool
     */
    private $resolvedFile;

    /**
     * @var AssetConfigInterface
     */
    private $assetConfig;

    /**
     * @param Source $source
     * @param ContextInterface $context
     * @param string $filePath
     * @param string $module
     * @param string $contentType
     * @param AssetConfigInterface $assetConfig
     */
    public function __construct(
        Source $source,
        ContextInterface $context,
        $filePath,
        $module,
        $contentType,
        AssetConfigInterface $assetConfig
    ) {
        $this->source = $source;
        $this->context = $context;
        $this->filePath = $filePath;
        $this->module = $module;
        $this->contentType = $contentType;
        $this->assetConfig = $assetConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->context->getBaseUrl() . $this->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceUrl()
    {
        return $this->context->getBaseUrl() . $this->getRelativeSourceFilePath();
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        $result = '';
        $result = $this->join($result, $this->context->getPath());
        $result = $this->join($result, $this->module);
        $result = $this->join($result, $this->filePath);
        if (
            $this->assetConfig->isAssetMinification($extension = pathinfo($result, PATHINFO_EXTENSION)) &&
            substr($result, -strlen($extension) - 5, 5) != '.min.'
        ) {
            $result = substr($result, 0, -strlen($extension)) . 'min.' . $extension;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativeSourceFilePath()
    {
        $path = $this->filePath;
        if (strpos($this->source->findRelativeSourceFilePath($this), 'less')) {
            $path = str_replace('.css', '.less', $this->filePath);
        }
        $result = '';
        $result = $this->join($result, $this->context->getPath());
        $result = $this->join($result, $this->module);
        $result = $this->join($result, $path);
        return $result;
    }

    /**
     * Subroutine for building path
     *
     * @param string $path
     * @param string $item
     * @return string
     */
    private function join($path, $item)
    {
        return trim($path . ($item ? '/' . $item : ''), '/');
    }

    /**
     * {@inheritdoc}
     * @throws File\NotFoundException if file cannot be resolved
     */
    public function getSourceFile()
    {
        if (null === $this->resolvedFile) {
            $this->resolvedFile = $this->source->getFile($this);
            if (false === $this->resolvedFile) {
                throw new File\NotFoundException("Unable to resolve the source file for '{$this->getPath()}'");
            }
        }
        return $this->resolvedFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return (string)$this->source->getContent($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * {@inheritdoc}
     * @return File\Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function getModule()
    {
        return $this->module;
    }
}
