<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Flat\Plugin;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Catalog\Model\Indexer\Category\Flat\State;

/**
 * Class \Magento\Catalog\Model\Indexer\Category\Flat\Plugin\StoreGroup
 *
 * @since 2.0.0
 */
class StoreGroup
{
    /**
     * @var bool
     * @since 2.2.0
     */
    private $needInvalidating;

    /**
     * @var IndexerRegistry
     * @since 2.0.0
     */
    protected $indexerRegistry;

    /**
     * @var State
     * @since 2.0.0
     */
    protected $state;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param State $state
     * @since 2.0.0
     */
    public function __construct(IndexerRegistry $indexerRegistry, State $state)
    {
        $this->indexerRegistry = $indexerRegistry;
        $this->state = $state;
    }

    /**
     * Validate changes for invalidating indexer
     *
     * @param AbstractModel $group
     * @return bool
     * @since 2.0.0
     */
    protected function validate(AbstractModel $group)
    {
        return $group->dataHasChangedFor('root_category_id') && !$group->isObjectNew();
    }

    /**
     * Check if need invalidate flat category indexer
     *
     * @param AbstractDb $subject
     * @param AbstractModel $group
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function beforeSave(AbstractDb $subject, AbstractModel $group)
    {
        $this->needInvalidating = $this->validate($group);
    }

    /**
     * Invalidate flat category indexer if root category changed for store group
     *
     * @param AbstractDb $subject
     * @param AbstractDb $objectResource
     *
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterSave(AbstractDb $subject, AbstractDb $objectResource)
    {
        if ($this->needInvalidating && $this->state->isFlatEnabled()) {
            $this->indexerRegistry->get(State::INDEXER_ID)->invalidate();
        }

        return $objectResource;
    }
}
