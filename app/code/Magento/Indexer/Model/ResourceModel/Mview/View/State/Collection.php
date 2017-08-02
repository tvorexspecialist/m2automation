<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\ResourceModel\Mview\View\State;

/**
 * Class \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection implements
    \Magento\Framework\Mview\View\State\CollectionInterface
{
    /**
     * Collection initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Indexer\Model\Mview\View\State::class,
            \Magento\Indexer\Model\ResourceModel\Mview\View\State::class
        );
    }
}
