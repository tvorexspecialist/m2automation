<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\NewRelicReporting\Model\Config;

/**
 * Class ReportSystemCacheFlush
 */
class ReportSystemCacheFlush implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\NewRelicReporting\Model\SystemFactory
     */
    protected $systemFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param Config $config
     * @param \Magento\NewRelicReporting\Model\SystemFactory $systemFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        Config $config,
        \Magento\NewRelicReporting\Model\SystemFactory $systemFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->config = $config;
        $this->systemFactory = $systemFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->dateTime = $dateTime;
    }

    /**
     * Reports a system cache flush to the database reporting_system_updates table
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            $jsonData = ['status' => 'updated'];

            $modelData = [
                'type' => Config::FLUSH_CACHE,
                'action' => $this->jsonEncoder->encode($jsonData),
                'updated_at' => $this->dateTime->formatDate(true),
            ];

            /** @var \Magento\NewRelicReporting\Model\System $systemModel */
            $systemModel = $this->systemFactory->create();
            $systemModel->setData($modelData);
            $systemModel->save();
        }
    }
}
