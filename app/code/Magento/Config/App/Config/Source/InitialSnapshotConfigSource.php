<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\FlagManager;

/**
 * The source with previously imported configuration.
 * @api
 * @since 2.2.0
 */
class InitialSnapshotConfigSource implements ConfigSourceInterface
{
    /**
     * The factory of Flag instances.
     *
     * @var FlagManager
     * @since 2.2.0
     */
    private $flagManager;

    /**
     * The factory of DataObject instances.
     *
     * @var DataObjectFactory
     * @since 2.2.0
     */
    private $dataObjectFactory;

    /**
     * @param FlagManager $flagManager The factory of Flag instances
     * @param DataObjectFactory $dataObjectFactory The factory of DataObject instances
     * @since 2.2.0
     */
    public function __construct(FlagManager $flagManager, DataObjectFactory $dataObjectFactory)
    {
        $this->flagManager = $flagManager;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Retrieves previously imported configuration.
     * Snapshots are stored in flags.
     *
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function get($path = '')
    {
        $flagData = (array)($this->flagManager->getFlagData('system_config_snapshot') ?: []);

        $data = $this->dataObjectFactory->create(
            ['data' => $flagData]
        );

        return $data->getData($path) ?: [];
    }
}
