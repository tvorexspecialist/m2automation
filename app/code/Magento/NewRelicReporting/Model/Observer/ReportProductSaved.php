<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\NewRelicReporting\Model\Config;

/**
 * Class ReportProductSaved
 * @since 2.0.0
 */
class ReportProductSaved implements ObserverInterface
{
    /**
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\NewRelicReporting\Model\SystemFactory
     * @since 2.0.0
     */
    protected $systemFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     * @since 2.0.0
     */
    protected $jsonEncoder;

    /**
     * @param Config $config
     * @param \Magento\NewRelicReporting\Model\SystemFactory $systemFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @since 2.0.0
     */
    public function __construct(
        Config $config,
        \Magento\NewRelicReporting\Model\SystemFactory $systemFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        $this->config = $config;
        $this->systemFactory = $systemFactory;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Reports any products created or updated to the database reporting_system_updates table
     *
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isNewRelicEnabled()) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $observer->getEvent()->getProduct();

            $jsonData = [
                'name' => $product->getName(),
            ];

            if ($product->isObjectNew()) {
                $jsonData['status'] = 'created';
            } else {
                $jsonData['id'] = $product->getId();
                $jsonData['status'] = 'updated';
            }

            $modelData = [
                'type' => Config::PRODUCT_CHANGE,
                'action' => $this->jsonEncoder->encode($jsonData),
            ];

            /** @var \Magento\NewRelicReporting\Model\System $systemModel */
            $systemModel = $this->systemFactory->create();
            $systemModel->setData($modelData);
            $systemModel->save();
        }
    }
}
