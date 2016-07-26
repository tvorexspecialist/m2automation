<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\AbstractPlugin;

class Group extends AbstractPlugin
{
    /**
     * @var bool
     */
    private $needInvalidation;

    /**
     * Check if indexer requires invalidation after store group save
     *
     * @param \Magento\Store\Model\ResourceModel\Group $subject
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Store\Model\ResourceModel\Group $subject,
        \Magento\Framework\Model\AbstractModel $group
    ) {
        $this->needInvalidation = !$group->isObjectNew() && $group->dataHasChangedFor('website_id');
    }

    /**
     * Invalidate indexer on store group save
     *
     * @param \Magento\Store\Model\ResourceModel\Group $subject
     * @param \Magento\Store\Model\ResourceModel\Group $result
     * @return \Magento\Store\Model\ResourceModel\Group
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Store\Model\ResourceModel\Group $subject,
        \Magento\Store\Model\ResourceModel\Group $result
    ) {
        if ($this->needInvalidation) {
            $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        }

        return $result;
    }

    /**
     * Invalidate indexer on store group delete
     *
     * @param \Magento\Store\Model\ResourceModel\Group $subject
     * @param \Magento\Store\Model\ResourceModel\Group $result
     *
     * @return \Magento\Store\Model\ResourceModel\Group
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\Store\Model\ResourceModel\Group $subject,
        \Magento\Store\Model\ResourceModel\Group $result
    ) {
        $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
        return $result;
    }
}
