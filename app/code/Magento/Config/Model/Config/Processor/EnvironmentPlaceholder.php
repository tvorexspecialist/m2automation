<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Processor;

use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Allows to extract configurations from environment variables.
 * @api
 * @since 2.1.3
 */
class EnvironmentPlaceholder implements PreProcessorInterface
{
    /**
     * @var PlaceholderFactory
     * @since 2.1.3
     */
    private $placeholderFactory;

    /**
     * @var ArrayManager
     * @since 2.1.3
     */
    private $arrayManager;

    /**
     * @var PlaceholderInterface
     * @since 2.1.3
     */
    private $placeholder;

    /**
     * @param PlaceholderFactory $placeholderFactory
     * @param ArrayManager $arrayManager
     * @since 2.1.3
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        ArrayManager $arrayManager
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->arrayManager = $arrayManager;
        $this->placeholder = $placeholderFactory->create(PlaceholderFactory::TYPE_ENVIRONMENT);
    }

    /**
     * Method extracts environment variables.
     * If environment variable is matching the desired rule - it's being used as value.
     *
     * {@inheritdoc}
     * @since 2.1.3
     */
    public function process(array $config)
    {
        $environmentVariables = $_ENV;

        foreach ($environmentVariables as $template => $value) {
            if (!$this->placeholder->isApplicable($template)) {
                continue;
            }

            $config = $this->arrayManager->set(
                $this->placeholder->restore($template),
                $config,
                $value
            );
        }

        return $config;
    }
}
