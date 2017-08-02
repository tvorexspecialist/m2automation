<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Staging theme model class
 */
namespace Magento\Theme\Model\Theme\Domain;

/**
 * Class \Magento\Theme\Model\Theme\Domain\Staging
 *
 * @since 2.0.0
 */
class Staging implements \Magento\Framework\View\Design\Theme\Domain\StagingInterface
{
    /**
     * Staging theme model instance
     *
     * @var \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    protected $_theme;

    /**
     * @var \Magento\Theme\Model\CopyService
     * @since 2.0.0
     */
    protected $_themeCopyService;

    /**
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Theme\Model\CopyService $themeCopyService
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Design\ThemeInterface $theme,
        \Magento\Theme\Model\CopyService $themeCopyService
    ) {
        $this->_theme = $theme;
        $this->_themeCopyService = $themeCopyService;
    }

    /**
     * Copy changes from 'staging' theme
     *
     * @return \Magento\Framework\View\Design\Theme\Domain\StagingInterface
     * @since 2.0.0
     */
    public function updateFromStagingTheme()
    {
        $this->_themeCopyService->copy($this->_theme, $this->_theme->getParentTheme());
        return $this;
    }
}
