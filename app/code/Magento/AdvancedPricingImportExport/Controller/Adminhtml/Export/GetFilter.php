<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Controller\Adminhtml\Export;

use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Magento\Framework\Controller\ResultFactory;

class GetFilter extends ExportController
{
    const CATALOG_PRODUCT = 'catalog_product';

    const ADVANCED_PRICING = 'advanced_pricing';

    /**
     * Get grid-filter of entity attributes action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest() && $data) {
            try {
                if($data['entity'] == self::ADVANCED_PRICING) {
                    $data['entity'] = self::CATALOG_PRODUCT;
                }
                /** @var \Magento\Framework\View\Result\Layout $resultLayout */
                $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
                /** @var $attrFilterBlock \Magento\ImportExport\Block\Adminhtml\Export\Filter */
                $attrFilterBlock = $resultLayout->getLayout()->getBlock('export.filter');
                /** @var $export \Magento\ImportExport\Model\Export */
                $export = $this->_objectManager->create('Magento\ImportExport\Model\Export');
                $export->setData($data);
                $export->filterAttributeCollection(
                    $attrFilterBlock->prepareCollection($export->getEntityAttributeCollection())
                );
                return $resultLayout;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Please correct the data sent.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }
}
