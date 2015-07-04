<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Resource;

use Magento\Sales\Model\Resource\Order\Creditmemo\Grid as CreditmemoGrid;
use Magento\Sales\Model\Resource\Order\Grid as OrderGrid;
use Magento\Sales\Model\Resource\Order\Shipment\Grid as ShipmentGrid;

class GridPool
{
    /**
     * @var GridInterface[]
     */
    protected $grids;

    /**
     * @param OrderGrid $orderGrid
     * @param \Magento\Sales\Model\Resource\AbstractGrid $invoiceGrid
     * @param ShipmentGrid $shipmentGrid
     * @param CreditmemoGrid $creditmemoGrid
     */
    public function __construct(
        OrderGrid $orderGrid,
        \Magento\Sales\Model\Resource\AbstractGrid $invoiceGrid,
        ShipmentGrid $shipmentGrid,
        CreditmemoGrid $creditmemoGrid
    ) {
        $this->grids = [
            'order_grid' => $orderGrid,
            'invoice_grid' => $invoiceGrid,
            'shipment_grid' => $shipmentGrid,
            'creditmemo_grid' => $creditmemoGrid,
        ];
    }

    /**
     * Refresh grids list
     *
     * @param int $orderId
     * @return $this
     */
    public function refreshByOrderId($orderId)
    {
        foreach ($this->grids as $grid) {
            $grid->refresh($orderId, 'sfo.entity_id');
        }
        return $this;
    }
}
