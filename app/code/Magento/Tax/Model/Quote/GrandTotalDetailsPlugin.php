<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Quote;

use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ObjectManager;

class GrandTotalDetailsPlugin
{
    /**
     * @var \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory
     */
    protected $detailsFactory;

    /**
     * @var \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory
     */
    protected $ratesFactory;

    /**
     * @var TotalSegmentExtensionFactory
     */
    protected $totalSegmentExtensionFactory;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory
     * @param \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory
     * @param TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory,
        \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory $ratesFactory,
        TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
        \Magento\Tax\Model\Config $taxConfig,
        SerializerInterface $serializer = null

    ) {
        $this->detailsFactory = $detailsFactory;
        $this->ratesFactory = $ratesFactory;
        $this->totalSegmentExtensionFactory = $totalSegmentExtensionFactory;
        $this->taxConfig = $taxConfig;
        $this->code = 'tax';
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * @param array $rates
     * @return array
     */
    protected function getRatesData($rates)
    {
        $taxRates = [];
        foreach ($rates as $rate) {
            $taxRate = $this->ratesFactory->create([]);
            $taxRate->setPercent($rate['percent']);
            $taxRate->setTitle($rate['title']);
            $taxRates[] = $taxRate;
        }
        return $taxRates;
    }

    /**
     * @param \Magento\Quote\Model\Cart\TotalsConverter $subject
     * @param \Magento\Quote\Api\Data\TotalSegmentInterface[] $totalSegments
     * @param \Magento\Quote\Model\Quote\Address\Total[] $addressTotals
     *
     * @return \Magento\Quote\Api\Data\TotalSegmentInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterProcess(
        \Magento\Quote\Model\Cart\TotalsConverter $subject,
        array $totalSegments,
        array $addressTotals = []
    ) {

        if (!array_key_exists($this->code, $addressTotals)) {
            return $totalSegments;
        }

        $taxes = $addressTotals['tax']->getData();
        if (!array_key_exists('full_info', $taxes)) {
            return $totalSegments;
        }

        $detailsId = 1;
        $finalData = [];
        $fullInfo = $taxes['full_info'];
        if (is_string($fullInfo)) {
            $fullInfo = $this->serializer->unserialize($fullInfo);
        }
        foreach ($fullInfo as $info) {
            if ((array_key_exists('hidden', $info) && $info['hidden'])
                || ($info['amount'] == 0 && $this->taxConfig->displayCartZeroTax())
            ) {
                continue;
            }

            $taxDetails = $this->detailsFactory->create([]);
            $taxDetails->setAmount($info['amount']);
            $taxRates = $this->getRatesData($info['rates']);
            $taxDetails->setRates($taxRates);
            $taxDetails->setGroupId($detailsId);
            $finalData[] = $taxDetails;
            $detailsId++;
        }
        $attributes = $totalSegments[$this->code]->getExtensionAttributes();
        if ($attributes === null) {
            $attributes = $this->totalSegmentExtensionFactory->create();
        }
        $attributes->setTaxGrandtotalDetails($finalData);
        $totalSegments[$this->code]->setExtensionAttributes($attributes);
        return $totalSegments;
    }
}
