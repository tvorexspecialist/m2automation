<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\ResourceModel\Indexer\State;

class Collection extends \Magento\Framework\Model\ModelResource\Db\Collection\AbstractCollection
{
    /**
     * Collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Indexer\Model\Indexer\State', 'Magento\Indexer\Model\ResourceModel\Indexer\State');
    }
}
