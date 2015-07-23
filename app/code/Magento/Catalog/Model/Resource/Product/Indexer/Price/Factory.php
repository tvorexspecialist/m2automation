<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Resource product indexer price factory
 */
namespace Magento\Catalog\Model\Resource\Product\Indexer\Price;

class Factory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create indexer price
     *
     * @param string $className
     * @param array $data
     * @return \Magento\Catalog\Model\Resource\Product\Indexer\Price\DefaultPrice
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($className, array $data = [])
    {
        $indexerPrice = $this->_objectManager->create($className, $data);

        if (!$indexerPrice instanceof \Magento\Catalog\Model\Resource\Product\Indexer\Price\DefaultPrice) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('%1 doesn\'t extend \Magento\Catalog\Model\Resource\Product\Indexer\Price\DefaultPrice', $className)
            );
        }
        return $indexerPrice;
    }
}
