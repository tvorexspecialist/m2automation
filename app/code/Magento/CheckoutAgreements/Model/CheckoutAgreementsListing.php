<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Model;

class CheckoutAgreementsListing implements \Magento\CheckoutAgreements\Api\CheckoutAgreementsListingInterface
{
    /**
     * Collection factory.
     *
     * @var \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $extAttributesJoinProcessor;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourceModel\Agreement\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->extAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getListing(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria) : array
    {
        /** @var $collection \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $this->extAttributesJoinProcessor->process($collection);
        return $collection->getItems();
    }
}
