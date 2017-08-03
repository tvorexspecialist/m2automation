<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\ResourceModel\Layout\Update;

/**
 * Layout update collection model
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     * @since 2.0.0
     */
    protected $dateTime;

    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'layout_update_collection';

    /**
     * Name of event parameter
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'layout_update_collection';

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Magento\Widget\Model\Layout\Update::class,
            \Magento\Widget\Model\ResourceModel\Layout\Update::class
        );
    }

    /**
     * Add filter by theme id
     *
     * @param int $themeId
     * @return $this
     * @since 2.0.0
     */
    public function addThemeFilter($themeId)
    {
        $this->_joinWithLink();
        $this->getSelect()->where('link.theme_id = ?', $themeId);

        return $this;
    }

    /**
     * Add filter by store id
     *
     * @param int $storeId
     * @return $this
     * @since 2.0.0
     */
    public function addStoreFilter($storeId)
    {
        $this->_joinWithLink();
        $this->getSelect()->where('link.store_id = ?', $storeId);

        return $this;
    }

    /**
     * Join with layout link table
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _joinWithLink()
    {
        $flagName = 'joined_with_link_table';
        if (!$this->getFlag($flagName)) {
            $this->getSelect()->join(
                ['link' => $this->getTable('layout_link')],
                'link.layout_update_id = main_table.layout_update_id',
                ['store_id', 'theme_id']
            );

            $this->setFlag($flagName, true);
        }

        return $this;
    }

    /**
     * Left Join with layout link table
     *
     * @param array $fields
     * @return $this
     * @since 2.0.0
     */
    protected function _joinLeftWithLink($fields = [])
    {
        $flagName = 'joined_left_with_link_table';
        if (!$this->getFlag($flagName)) {
            $this->getSelect()->joinLeft(
                ['link' => $this->getTable('layout_link')],
                'link.layout_update_id = main_table.layout_update_id',
                [$fields]
            );
            $this->setFlag($flagName, true);
        }

        return $this;
    }

    /**
     * Get layouts that are older then specified number of days
     *
     * @param string $days
     * @return $this
     * @since 2.0.0
     */
    public function addUpdatedDaysBeforeFilter($days)
    {
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $storeInterval = new \DateInterval('P' . $days . 'D');
        $datetime->sub($storeInterval);
        $formattedDate = $this->dateTime->formatDate($datetime->getTimestamp());

        $this->addFieldToFilter(
            'main_table.updated_at',
            ['notnull' => true]
        )->addFieldToFilter(
            'main_table.updated_at',
            ['lt' => $formattedDate]
        );

        return $this;
    }

    /**
     * Get layouts without links
     *
     * @return $this
     * @since 2.0.0
     */
    public function addNoLinksFilter()
    {
        $this->_joinLeftWithLink();
        $this->addFieldToFilter('link.layout_update_id', ['null' => true]);

        return $this;
    }

    /**
     * Delete updates in collection
     *
     * @return $this
     * @since 2.0.0
     */
    public function delete()
    {
        /** @var $update \Magento\Widget\Model\Layout\Update */
        foreach ($this->getItems() as $update) {
            $update->delete();
        }
        return $this;
    }
}
