<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Associated;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;

class Grid extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param LayoutFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function executeInternal()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
