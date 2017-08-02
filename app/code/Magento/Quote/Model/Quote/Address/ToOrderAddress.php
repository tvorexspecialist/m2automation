<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Quote\Address;

use Magento\Framework\DataObject\Copy;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\Order\AddressRepository as OrderAddressRepository;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Class ToOrderAddress
 * @since 2.0.0
 */
class ToOrderAddress
{
    /**
     * @var Copy
     * @since 2.0.0
     */
    protected $objectCopyService;

    /**
     * @var OrderAddressRepository
     * @since 2.0.0
     */
    protected $orderAddressRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * @since 2.0.0
     */
    protected $dataObjectHelper;

    /**
     * @param OrderAddressRepository $orderAddressRepository
     * @param Copy $objectCopyService
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @since 2.0.0
     */
    public function __construct(
        OrderAddressRepository $orderAddressRepository,
        Copy $objectCopyService,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->orderAddressRepository = $orderAddressRepository;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param Address $object
     * @param array $data
     * @return OrderAddressInterface
     * @since 2.0.0
     */
    public function convert(Address $object, $data = [])
    {
        $orderAddress = $this->orderAddressRepository->create();

        $orderAddressData = $this->objectCopyService->getDataFromFieldset(
            'sales_convert_quote_address',
            'to_order_address',
            $object
        );

        $this->dataObjectHelper->populateWithArray(
            $orderAddress,
            array_merge($orderAddressData, $data),
            \Magento\Sales\Api\Data\OrderAddressInterface::class
        );

        return $orderAddress;
    }
}
