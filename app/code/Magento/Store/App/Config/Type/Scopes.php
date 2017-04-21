<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Config\Type;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\DataObject;

/**
 * Merge and hold scopes data from different sources
 */
class Scopes implements ConfigTypeInterface
{
    const CONFIG_TYPE = 'scopes';

    /**
     * @var ConfigSourceInterface
     */
    private $source;

    /**
     * @var DataObject[]
     */
    private $data;

    /**
     * System constructor.
     * @param ConfigSourceInterface $source
     */
    public function __construct(
        ConfigSourceInterface $source
    ) {
        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        if (!$this->data) {
            $this->data = new DataObject($this->source->get());
        }

        return $this->data->getData($path);
    }

    /**
     * Clean cache
     *
     * @return void
     */
    public function clean()
    {
        $this->data = null;
    }
}
