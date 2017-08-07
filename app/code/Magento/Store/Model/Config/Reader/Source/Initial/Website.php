<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader\Source\Initial;

use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Framework\App\Config\Scope\Converter;

/**
 * Class for retrieving configuration from initial config by website scope
 * @since 2.1.3
 */
class Website implements SourceInterface
{
    /**
     * @var Initial
     * @since 2.1.3
     */
    private $initialConfig;

    /**
     * @var DefaultScope
     * @since 2.1.3
     */
    private $defaultScope;

    /**
     * @var Converter
     * @since 2.1.3
     */
    private $converter;

    /**
     * @param Initial $initialConfig
     * @param DefaultScope $defaultScope
     * @param Converter $converter
     * @since 2.1.3
     */
    public function __construct(
        Initial $initialConfig,
        DefaultScope $defaultScope,
        Converter $converter
    ) {
        $this->initialConfig = $initialConfig;
        $this->defaultScope = $defaultScope;
        $this->converter = $converter;
    }

    /**
     * Retrieve config by website scope
     *
     * @param string|null $scopeCode
     * @return array
     * @since 2.1.3
     */
    public function get($scopeCode = null)
    {
        return $this->converter->convert(array_replace_recursive(
            $this->defaultScope->get(),
            $this->initialConfig->getData("websites|{$scopeCode}")
        ));
    }
}
