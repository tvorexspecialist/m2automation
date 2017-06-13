<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\Plugin;

use Magento\Config\App\Config\Source\DumpConfigSourceAggregated;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Framework\View\DesignInterface;

/**
 * This is plugin for Magento\Framework\App\Config\Scope\Converter class.
 *
 * Detects the design theme configuration data (path ) and convert theme identifier from theme_id to theme_full_path.
 */
class DesignConfigTheme
{
    /**
     * @var ListInterface
     */
    private $themeList;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ListInterface $themeList
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ListInterface $themeList,
        ArrayManager $arrayManager
    ) {
        $this->themeList = $themeList;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param DumpConfigSourceAggregated $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(DumpConfigSourceAggregated $subject, $result)
    {
        foreach ($result as $scope => &$item) {
            if ($scope === \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $item = $this->changeThemeIdToFullPath($item);
            } else {
                foreach ($item as &$scopeItems) {
                    $scopeItems = $this->changeThemeIdToFullPath($scopeItems);
                }
            }
        }

        return $result;
    }

    /**
     * Check \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID config path
     * and convert theme_id to full_theme_path. Ex. "frontend/Magento/blank"
     *
     * @param array $configItems
     * @return array
     */
    private function changeThemeIdToFullPath($configItems)
    {
        if ($this->arrayManager->exists(DesignInterface::XML_PATH_THEME_ID, $configItems)) {
            $themeIdentifier = $this->arrayManager->get(DesignInterface::XML_PATH_THEME_ID, $configItems);
            if (is_numeric($themeIdentifier)) {
                $theme = $this->themeList->getItemById($themeIdentifier);
            } else {
                $theme = $this->themeList->getThemeByFullPath($themeIdentifier);
            }

            if ($theme && $theme->getId()) {
                return $this->arrayManager->set(DesignInterface::XML_PATH_THEME_ID, $configItems, $theme->getFullPath());
            }
        }

        return $configItems;
    }
}
