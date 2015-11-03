<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TaxImportExport\Controller\Adminhtml\Rate;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class ExportXml extends \Magento\TaxImportExport\Controller\Adminhtml\Rate
{
    /**
     * Export rates grid to XML format
     *
     * @return ResponseInterface
     */
    public function executeInternal()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $content = $resultLayout->getLayout()->getChildBlock('adminhtml.tax.rate.grid', 'grid.export');

        return $this->fileFactory->create(
            'rates.xml',
            $content->getExcelFile(),
            DirectoryList::VAR_DIR
        );
    }
}
