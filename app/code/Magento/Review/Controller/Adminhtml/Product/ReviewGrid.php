<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\RatingFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Review\Controller\Adminhtml\Product\ReviewGrid
 *
 * @since 2.0.0
 */
class ReviewGrid extends ProductController
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     * @since 2.0.0
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        LayoutFactory $layoutFactory
    ) {
        $this->layoutFactory = $layoutFactory;
        parent::__construct($context, $coreRegistry, $reviewFactory, $ratingFactory);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     * @since 2.0.0
     */
    public function execute()
    {
        $layout = $this->layoutFactory->create();
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setContents($layout->createBlock(\Magento\Review\Block\Adminhtml\Grid::class)->toHtml());
        return $resultRaw;
    }
}
