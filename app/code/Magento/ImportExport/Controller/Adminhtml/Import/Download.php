<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\ImportExport\Controller\Adminhtml\Import as ImportController;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Download sample file controller
 */
class Download extends ImportController
{
    const SAMPLE_FILES_DIRECTORY = 'Magento/ImportExport/Files/Sample/';

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $fileDirectory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct(
            $context
        );
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->fileDirectory = $filesystem->getDirectoryWrite(DirectoryList::MODULES);
    }

    /**
     * Download sample file action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $fileName = $this->getRequest()->getParam('filename') . '.csv';
        $filePath = self::SAMPLE_FILES_DIRECTORY . $fileName;

        if (!$this->fileDirectory->isFile($filePath)) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $this->messageManager->addError(__('There is no sample file for this entity.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/import');
            return $resultRedirect;
        }

        $fileSize = isset($this->fileDirectory->stat($filePath)['size'])
            ? $this->fileDirectory->stat($filePath)['size'] : null;

        $this->fileFactory->create(
            $fileName,
            null,
            DirectoryList::VAR_DIR,
            'application/octet-stream',
            $fileSize
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($this->fileDirectory->readFile($filePath));
        return $resultRaw;
    }
}
