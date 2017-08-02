<?php
/**
 * Review renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Product;

use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product;

/**
 * Class \Magento\Review\Block\Product\ReviewRenderer
 *
 * @since 2.0.0
 */
class ReviewRenderer extends \Magento\Framework\View\Element\Template implements ReviewRendererInterface
{
    /**
     * Array of available template name
     *
     * @var array
     * @since 2.0.0
     */
    protected $_availableTemplates = [
        self::FULL_VIEW => 'helper/summary.phtml',
        self::SHORT_VIEW => 'helper/summary_short.phtml',
    ];

    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     * @since 2.0.0
     */
    protected $_reviewFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        array $data = []
    ) {
        $this->_reviewFactory = $reviewFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get review summary html
     *
     * @param Product $product
     * @param string $templateType
     * @param bool $displayIfNoReviews
     *
     * @return string
     * @since 2.0.0
     */
    public function getReviewsSummaryHtml(
        \Magento\Catalog\Model\Product $product,
        $templateType = self::DEFAULT_VIEW,
        $displayIfNoReviews = false
    ) {
        if (!$product->getRatingSummary() && !$displayIfNoReviews) {
            return '';
        }
        // pick template among available
        if (empty($this->_availableTemplates[$templateType])) {
            $templateType = self::DEFAULT_VIEW;
        }
        $this->setTemplate($this->_availableTemplates[$templateType]);

        $this->setDisplayIfEmpty($displayIfNoReviews);

        if (!$product->getRatingSummary()) {
            $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
        }
        $this->setProduct($product);

        return $this->toHtml();
    }

    /**
     * Get ratings summary
     *
     * @return string
     * @since 2.0.0
     */
    public function getRatingSummary()
    {
        return $this->getProduct()->getRatingSummary()->getRatingSummary();
    }

    /**
     * Get count of reviews
     *
     * @return int
     * @since 2.0.0
     */
    public function getReviewsCount()
    {
        return $this->getProduct()->getRatingSummary()->getReviewsCount();
    }

    /**
     * Get review product list url
     *
     * @param bool $useDirectLink allows to use direct link for product reviews page
     * @return string
     * @since 2.0.0
     */
    public function getReviewsUrl($useDirectLink = false)
    {
        $product = $this->getProduct();
        if ($useDirectLink) {
            return $this->getUrl(
                'review/product/list',
                ['id' => $product->getId(), 'category' => $product->getCategoryId()]
            );
        }
        return $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
    }
}
