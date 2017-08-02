<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Cart source
 * @since 2.0.0
 */
class DirectoryData implements SectionSourceInterface
{
    /**
     * @var \Magento\Directory\Helper\Data
     * @since 2.0.0
     */
    protected $directoryHelper;

    /**
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(\Magento\Directory\Helper\Data $directoryHelper)
    {
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSectionData()
    {
        $output = [];
        $regionsData = $this->directoryHelper->getRegionData();
        /**
         * @var string $code
         * @var \Magento\Directory\Model\Country $data
         */
        foreach ($this->directoryHelper->getCountryCollection() as $code => $data) {
            $output[$code]['name'] = $data->getName();
            if (array_key_exists($code, $regionsData)) {
                foreach ($regionsData[$code] as $key => $region) {
                    $output[$code]['regions'][$key]['code'] = $region['code'];
                    $output[$code]['regions'][$key]['name'] = $region['name'];
                }
            }
        }
        return $output;
    }
}
