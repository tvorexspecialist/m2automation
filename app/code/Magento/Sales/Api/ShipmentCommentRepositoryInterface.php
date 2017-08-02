<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Shipment comment repository interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package. A shipment document can contain comments.
 * @api
 * @since 2.0.0
 */
interface ShipmentCommentRepositoryInterface
{
    /**
     * Lists shipment comments that match specific search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\ShipmentCommentSearchResultInterface Shipment comment search result interface.
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Loads a specified shipment comment.
     *
     * @param int $id The shipment comment ID.
     * @return \Magento\Sales\Api\Data\ShipmentCommentInterface Shipment comment interface.
     * @since 2.0.0
     */
    public function get($id);

    /**
     * Deletes a specified shipment comment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentCommentInterface $entity The shipment comment.
     * @return bool
     * @since 2.0.0
     */
    public function delete(\Magento\Sales\Api\Data\ShipmentCommentInterface $entity);

    /**
     * Performs persist operations for a specified shipment comment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentCommentInterface $entity The shipment comment.
     * @return \Magento\Sales\Api\Data\ShipmentCommentInterface Shipment comment interface.
     * @since 2.0.0
     */
    public function save(\Magento\Sales\Api\Data\ShipmentCommentInterface $entity);
}
