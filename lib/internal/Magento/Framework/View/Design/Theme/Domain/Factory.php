<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Domain;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme domain model class factory
 * @since 2.0.0
 */
class Factory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Types
     *
     * @var array
     * @since 2.0.0
     */
    protected $_types = [
        ThemeInterface::TYPE_PHYSICAL => \Magento\Framework\View\Design\Theme\Domain\PhysicalInterface::class,
        ThemeInterface::TYPE_VIRTUAL => \Magento\Framework\View\Design\Theme\Domain\VirtualInterface::class,
        ThemeInterface::TYPE_STAGING => \Magento\Framework\View\Design\Theme\Domain\StagingInterface::class,
    ];

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new config object
     *
     * @param ThemeInterface $theme
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function create(ThemeInterface $theme)
    {
        if (!isset($this->_types[$theme->getType()])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Invalid type of theme domain model "%1"', [$theme->getType()])
            );
        }
        $class = $this->_types[$theme->getType()];
        return $this->_objectManager->create($class, ['theme' => $theme]);
    }
}
