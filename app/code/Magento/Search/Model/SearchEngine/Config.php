<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\SearchEngine;

class Config implements \Magento\Framework\Search\SearchEngine\ConfigInterface
{
    /**
     * Search engine config data storage
     *
     * @var Config\Data
     */
    protected $dataStorage;

    /**
     * Constructor
     *
     * @param Config\Data $dataStorage
     */
    public function __construct(Config\Data $dataStorage)
    {
        $this->dataStorage = $dataStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeclaredFeatures($searchEngine)
    {
        return $this->dataStorage->get($searchEngine) ?: [];
    }
}
