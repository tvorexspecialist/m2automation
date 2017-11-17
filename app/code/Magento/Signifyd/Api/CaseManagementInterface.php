<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api;

use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Signifyd management interface
 * Allows to performs operations with Signifyd cases.
 *
 * @api
 */
interface CaseManagementInterface
{
    /**
     * Creates new Case entity linked to order id.
     *
     * @param int $orderId
     * @return CaseInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException If case for $orderId already exists
     */
    public function create($orderId);

    /**
     * Gets Case entity associated with order id.
     *
     * @param int $orderId
     * @return CaseInterface|null
     */
    public function getByOrderId($orderId);

    /**
     * Gets Case entity by received case (investigation) id.
     *
     * @param int $caseId
     * @return CaseInterface|null
     */
    public function getByCaseId($caseId);
}
