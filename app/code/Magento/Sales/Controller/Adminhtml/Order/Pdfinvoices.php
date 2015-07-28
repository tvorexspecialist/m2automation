<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\Resource\Order\Invoice\CollectionFactory;
use Magento\Sales\Model\Resource\Order\Collection as OrderCollection;

class Pdfinvoices extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Invoice
     */
    protected $pdfInvoice;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param Filter $filter
     * @param Invoice $pdfInvoice
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Context $context,
        DateTime $dateTime,
        FileFactory $fileFactory,
        Filter $filter,
        Invoice $pdfInvoice
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfInvoice = $pdfInvoice;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $filter);
    }

    /**
     * Print invoices for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        $invoicesCollection = $this->collectionFactory->create()->setOrderFilter(['in' => $collection->getAllIds()]);
        if (!$invoicesCollection->getSize()) {
            $this->messageManager->addError(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath('sales/*/');
        }
        return $this->fileFactory->create(
            sprintf('packingslip%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $this->pdfInvoice->getPdf($invoicesCollection->getItems())->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
