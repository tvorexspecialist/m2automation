<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\Message\Renderer;

class MessageConfigurationsPool
{
    /**
     * Key of instance is the exception format parameter
     *
     * @var MessageConfigurationInterface[]
     */
    private $messageConfigurationsMap = [];

    /**
     * @param MessageConfigurationInterface[] $messageConfigurationsMap
     */
    public function __construct(array $messageConfigurationsMap)
    {
        $this->messageConfigurationsMap = $messageConfigurationsMap;
    }

    /**
     * Renders an exception
     *
     * @param \Exception $exception
     * @return MessageConfigurationInterface|null
     */
    public function getMessageConfiguration(\Exception $exception)
    {
        if (isset($this->messageConfigurationsMap[get_class($exception)])) {
            return $this->messageConfigurationsMap[get_class($exception)];
        }
    }
}
