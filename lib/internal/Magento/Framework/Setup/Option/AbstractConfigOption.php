<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Option;

use Symfony\Component\Console\Input\InputOption;

/**
 * Abstract Option class in deployment configuration tool
 * @since 2.0.0
 */
abstract class AbstractConfigOption extends InputOption
{
    /**
     * Frontend input type
     *
     * @var string
     * @since 2.0.0
     */
    private $frontendType;

    /**
     * Config path
     *
     * @var string
     * @since 2.0.0
     */
    private $configPath;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $frontendType
     * @param int $mode
     * @param string $configPath
     * @param string $description
     * @param string|array|null $defaultValue
     * @param string|array|null $shortcut
     * @since 2.0.0
     */
    public function __construct(
        $name,
        $frontendType,
        $mode,
        $configPath,
        $description = '',
        $defaultValue = null,
        $shortcut = null
    ) {
        $this->frontendType = $frontendType;
        $this->configPath = $configPath;
        parent::__construct($name, $shortcut, $mode, $description, $defaultValue);
    }

    /**
     * Get frontend input type
     *
     * @return string
     * @since 2.0.0
     */
    public function getFrontendType()
    {
        return $this->frontendType;
    }

    /**
     * Get config path
     *
     * @return string
     * @since 2.0.0
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * No base validation
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param mixed $data
     * @return void
     * @since 2.0.0
     */
    public function validate($data)
    {
    }
}
