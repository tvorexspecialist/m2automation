<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Framework\Indexer\ConfigInterface;

/**
 * Recurring data upgrade for indexer module
 * @since 2.2.0
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var IndexerFactory
     * @since 2.2.0
     */
    private $indexerFactory;

    /**
     * @var ConfigInterface
     * @since 2.2.0
     */
    private $configInterface;

    /**
     * RecurringData constructor.
     *
     * @param IndexerFactory $indexerFactory
     * @param ConfigInterface $configInterface
     * @since 2.2.0
     */
    public function __construct(
        IndexerFactory $indexerFactory,
        ConfigInterface $configInterface
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->configInterface = $configInterface;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        foreach (array_keys($this->configInterface->getIndexers()) as $indexerId) {
            $indexer = $this->indexerFactory->create()->load($indexerId);
            if ($indexer->isScheduled()) {
                $indexer->getView()->unsubscribe()->subscribe();
            }
        }
    }
}
