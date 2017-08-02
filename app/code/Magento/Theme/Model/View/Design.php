<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\View;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Keeps design settings for current request
 * @since 2.0.0
 */
class Design implements \Magento\Framework\View\DesignInterface
{
    /**
     * Package area
     *
     * @var string
     * @since 2.0.0
     */
    protected $_area;

    /**
     * Package theme
     *
     * @var \Magento\Theme\Model\Theme
     * @since 2.0.0
     */
    protected $_theme;

    /**
     * Directory of the css file
     * Using only to transmit additional parameter in callback functions
     *
     * @var string
     * @since 2.0.0
     */
    protected $_callbackFileDir;

    /**
     * Store list manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory
     * @since 2.0.0
     */
    protected $_flyweightFactory;

    /**
     * @var \Magento\Theme\Model\ThemeFactory
     * @since 2.0.0
     */
    protected $_themeFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $_locale;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\State
     * @since 2.0.0
     */
    protected $_appState;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Design\Theme\FlyweightFactory $flyweightFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Theme\Model\ThemeFactory $themeFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\State $appState
     * @param array $themes
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Design\Theme\FlyweightFactory $flyweightFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\State $appState,
        array $themes
    ) {
        $this->_storeManager = $storeManager;
        $this->_flyweightFactory = $flyweightFactory;
        $this->_themeFactory = $themeFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_appState = $appState;
        $this->_themes = $themes;
        $this->objectManager = $objectManager;
    }

    /**
     * Set package area
     *
     * @param string $area
     * @return $this
     * @since 2.0.0
     */
    public function setArea($area)
    {
        $this->_area = $area;
        $this->_theme = null;
        return $this;
    }

    /**
     * Retrieve package area
     *
     * @return string
     * @since 2.0.0
     */
    public function getArea()
    {
        // In order to support environment emulation of area, if area is set, return it
        if ($this->_area && !$this->_appState->isAreaCodeEmulated()) {
            return $this->_area;
        }
        return $this->_appState->getAreaCode();
    }

    /**
     * Set theme path
     *
     * @param \Magento\Framework\View\Design\ThemeInterface|string $theme
     * @param string $area
     * @return $this
     * @since 2.0.0
     */
    public function setDesignTheme($theme, $area = null)
    {
        if ($area) {
            $this->setArea($area);
        } else {
            $area = $this->getArea();
        }

        if ($theme instanceof \Magento\Framework\View\Design\ThemeInterface) {
            $this->_theme = $theme;
        } else {
            $this->_theme = $this->_flyweightFactory->create($theme, $area);
        }

        return $this;
    }

    /**
     * Get default theme which declared in configuration
     *
     * Write default theme to core_config_data
     *
     * @param string|null $area
     * @param array $params
     * @return string|int
     * @since 2.0.0
     */
    public function getConfigurationDesignTheme($area = null, array $params = [])
    {
        if (!$area) {
            $area = $this->getArea();
        }

        $theme = null;
        $store = isset($params['store']) ? $params['store'] : null;

        if ($this->_isThemePerStoreView($area)) {
            if ($this->_storeManager->isSingleStoreMode()) {
                $theme = $this->_scopeConfig->getValue(
                    self::XML_PATH_THEME_ID,
                    ScopeInterface::SCOPE_WEBSITES
                );
            } else {
                $theme = (string) $this->_scopeConfig->getValue(
                    self::XML_PATH_THEME_ID,
                    ScopeInterface::SCOPE_STORE,
                    $store
                );
            }
        }

        if (!$theme && isset($this->_themes[$area])) {
            $theme = $this->_themes[$area];
        }

        return $theme;
    }

    /**
     * Whether themes in specified area are supposed to be configured per store view
     *
     * @param string $area
     * @return bool
     * @since 2.0.0
     */
    private function _isThemePerStoreView($area)
    {
        return $area == self::DEFAULT_AREA;
    }

    /**
     * Set default design theme
     *
     * @return $this
     * @since 2.0.0
     */
    public function setDefaultDesignTheme()
    {
        $this->setDesignTheme($this->getConfigurationDesignTheme());
        return $this;
    }

    /**
     * Design theme model getter
     *
     * @return \Magento\Theme\Model\Theme
     * @since 2.0.0
     */
    public function getDesignTheme()
    {
        if ($this->_theme === null) {
            $this->_theme = $this->_themeFactory->create();
        }
        return $this->_theme;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getThemePath(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $themePath = $theme->getThemePath();
        if (!$themePath) {
            $themeId = $theme->getId();
            if ($themeId) {
                $themePath = self::PUBLIC_THEME_DIR . $themeId;
            } else {
                $themePath = self::PUBLIC_VIEW_DIR;
            }
        }
        return $themePath;
    }

    /**
     * Get locale
     *
     * @return string
     * @since 2.0.0
     */
    public function getLocale()
    {
        if (null === $this->_locale) {
            $this->_locale = $this->objectManager->get(\Magento\Framework\Locale\ResolverInterface::class);
        }
        return $this->_locale->getLocale();
    }

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     * @return $this
     * @since 2.2.0
     */
    public function setLocale(\Magento\Framework\Locale\ResolverInterface $locale)
    {
        $this->_locale = $locale;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDesignParams()
    {
        $params = [
            'area' => $this->getArea(),
            'themeModel' => $this->getDesignTheme(),
            'locale'     => $this->getLocale(),
        ];

        return $params;
    }
}
