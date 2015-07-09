<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;

class Minification
{
    /**
     * XML path for asset minification configuration
     */
    const XML_PATH_MINIFICATION_ENABLED = 'dev/%s/minify_files';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var State
     */
    private $appState;
    /**
     * @var string
     */
    private $scope;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param State $appState
     * @param string $scope
     */
    public function __construct(ScopeConfigInterface $scopeConfig, State $appState, $scope = 'store')
    {
        $this->scopeConfig = $scopeConfig;
        $this->appState = $appState;
        $this->scope = $scope;
    }

    /**
     * Check whether asset minification is on for specified content type
     *
     * @param string $contentType
     * @return bool
     */
    public function isEnabled($contentType)
    {
        return
            $this->appState->getMode() != State::MODE_DEVELOPER &&
            (bool)$this->scopeConfig->isSetFlag(
                sprintf(self::XML_PATH_MINIFICATION_ENABLED, $contentType),
                $this->scope
            );
    }

    /**
     * Add 'min' suffix if minification is enabled and $filename has no one.
     *
     * @param string $filename
     * @return string
     */
    public function addMinifiedSign($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if (
            $this->isEnabled($extension) &&
            !$this->isMinifiedFilename($filename)
        ) {
            $filename = substr($filename, 0, -strlen($extension)) . 'min.' . $extension;
        }
        return $filename;
    }

    /**
     * Remove 'min' suffix if exists and minification is enabled
     *
     * @param string $filename
     * @return string
     */
    public function removeMinifiedSign($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if (
            $this->isEnabled($extension) &&
            $this->isMinifiedFilename($filename)
        ) {
            $filename = substr($filename, 0, -strlen($extension) - 4) . $extension;
        }
        return $filename;
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function isMinifiedFilename($filename)
    {
        return substr($filename, strrpos($filename, '.') - 4, 5) == '.min.';
    }
}
