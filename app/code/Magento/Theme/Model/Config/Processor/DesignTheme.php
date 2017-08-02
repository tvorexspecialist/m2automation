<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Config\Processor;

use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\DesignInterface;

/**
 * Allows to convert configurations from \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID variables.
 *
 * Detects the design theme configuration data (path \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID)
 * and convert theme identifier from theme_full_path (Ex. "frontend/Magento/blank") to theme_id.
 * @since 2.2.0
 */
class DesignTheme implements PreProcessorInterface
{
    /**
     * @var ArrayManager
     * @since 2.2.0
     */
    private $arrayManager;

    /**
     * @var ListInterface
     * @since 2.2.0
     */
    private $themeList;

    /**
     * @param ArrayManager $arrayManager
     * @param ListInterface $themeList
     * @since 2.2.0
     */
    public function __construct(
        ArrayManager $arrayManager,
        ListInterface $themeList
    ) {
        $this->arrayManager = $arrayManager;
        $this->themeList = $themeList;
    }

    /**
     * Change value from theme_full_path (Ex. "frontend/Magento/blank") to theme_id field for every existed scope.
     * All other values leave without changes.
     *
     * @param array $config
     * @return array
     * @since 2.2.0
     */
    public function process(array $config)
    {
        foreach ($config as $scope => &$item) {
            if ($scope === \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $item = $this->changeThemeFullPathToIdentifier($item);
            } else {
                foreach ($item as &$scopeItems) {
                    $scopeItems = $this->changeThemeFullPathToIdentifier($scopeItems);
                }
            }
        }

        return $config;
    }

    /**
     * Check \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID config path
     * and convert theme_full_path (Ex. "frontend/Magento/blank") to theme_id
     *
     * @param array $configItems
     * @return array
     * @since 2.2.0
     */
    private function changeThemeFullPathToIdentifier($configItems)
    {
        $theme = null;
        if ($this->arrayManager->exists(DesignInterface::XML_PATH_THEME_ID, $configItems)) {
            $themeIdentifier = $this->arrayManager->get(DesignInterface::XML_PATH_THEME_ID, $configItems);
            if (!is_numeric($themeIdentifier)) {
                // workaround for case when db is not available
                try {
                    $theme = $this->themeList->getThemeByFullPath($themeIdentifier);
                } catch (\DomainException $domainException) {
                    $theme = null;
                }
            }

            if ($theme && $theme->getId()) {
                return $this->arrayManager->set(DesignInterface::XML_PATH_THEME_ID, $configItems, $theme->getId());
            }
        }

        return $configItems;
    }
}
