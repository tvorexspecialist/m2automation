<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Customer;

use Magento\Review\Model\ResourceModel\Review\Product\Collection;

/**
 * Recent Customer Reviews Block
 *
 * @api
 * @since 2.0.0
 */
class Recent extends \Magento\Framework\View\Element\Template
{
    /**
     * Customer list template name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'customer/list.phtml';

    /**
     * Product reviews collection
     *
     * @var Collection
     * @since 2.0.0
     */
    protected $_collection;

    /**
     * Review resource model
     *
     * @var \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory
     * @since 2.0.0
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     * @since 2.0.0
     */
    protected $currentCustomer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * Truncate string
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     * @return string
     * @since 2.0.0
     */
    public function truncateString($value, $length = 80, $etc = '...', &$remainder = '', $breakWords = true)
    {
        return $this->filterManager->truncate(
            $value,
            ['length' => $length, 'etc' => $etc, 'remainder' => $remainder, 'breakWords' => $breakWords]
        );
    }

    /**
     * Return collection of reviews
     *
     * @return array|\Magento\Review\Model\ResourceModel\Review\Product\Collection
     * @since 2.0.0
     */
    public function getReviews()
    {
        if (!($customerId = $this->currentCustomer->getCustomerId())) {
            return [];
        }
        if (!$this->_collection) {
            $this->_collection = $this->_collectionFactory->create();
            $this->_collection
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addCustomerFilter($customerId)
                ->setDateOrder()
                ->setPageSize(5)
                ->load()
                ->addReviewSummary();
        }
        return $this->_collection;
    }

    /**
     * Return review customer view url
     *
     * @return string
     * @since 2.0.0
     */
    public function getReviewLink()
    {
        return $this->getUrl('review/customer/view/');
    }

    /**
     * Return catalog product view url
     *
     * @return string
     * @since 2.0.0
     */
    public function getProductLink()
    {
        return $this->getUrl('catalog/product/view/');
    }

    /**
     * Format review date
     *
     * @param string $date
     * @return string
     * @since 2.0.0
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::SHORT);
    }

    /**
     * Return review customer url
     *
     * @return string
     * @since 2.0.0
     */
    public function getAllReviewsUrl()
    {
        return $this->getUrl('review/customer');
    }

    /**
     * Return review customer view url for a specific customer/review
     *
     * @param int $id
     * @return string
     * @since 2.0.0
     */
    public function getReviewUrl($id)
    {
        return $this->getUrl('review/customer/view', ['id' => $id]);
    }
}
