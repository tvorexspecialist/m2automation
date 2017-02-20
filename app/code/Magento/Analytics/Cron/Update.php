<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Cron;

use Magento\Analytics\Model\Connector;
use Magento\Analytics\Model\FlagManager;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\AdminNotification\Model\ResourceModel\Inbox as InboxResource;
use Magento\Analytics\Model\Plugin\BaseUrlConfigPlugin;

/**
 * Class Update
 */
class Update
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * Reinitable Config Model.
     *
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;
    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * Update constructor.
     * @param Connector $connector
     * @param WriterInterface $configWriter
     * @param ReinitableConfigInterface $reinitableConfig
     * @param FlagManager $flagManager
     */
    public function __construct(
        Connector $connector,
        WriterInterface $configWriter,
        ReinitableConfigInterface $reinitableConfig,
        FlagManager $flagManager
    ) {
        $this->connector = $connector;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
        $this->flagManager = $flagManager;
    }

    /**
     * Execute scheduled update operation
     *
     * @return bool
     */
    public function execute()
    {
        $signUpResult = $this->connector->execute('update');
        if ($signUpResult === false) {
            return false;
        }
        $this->configWriter->delete(BaseUrlConfigPlugin::UPDATE_CRON_STRING_PATH);
        $this->flagManager->deleteFlag(BaseUrlConfigPlugin::OLD_BASE_URL_FLAG_CODE);
        $this->reinitableConfig->reinit();
        return true;
    }
}
