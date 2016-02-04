<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory;

/**
 * Mass-Delete Controller
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        $synonymGroupRepository = $this->_objectManager->create('Magento\Search\Api\SynonymGroupRepositoryInterface');

        $deletedItems = 0;
        foreach ($collection as $synonymGroup) {
            try {
                $synonymGroupRepository->delete($synonymGroup);
                $deletedItems++;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        if ($deletedItems != 0) {
            if ($collectionSize != $deletedItems) {
                $this->messageManager->addError(
                    __('Failed to delete %1 synonym group(s).', $collectionSize - $deletedItems)
                );
            }

            $this->messageManager->addSuccess(
                __('A total of %1 synonym group(s) have been deleted.', $deletedItems)
            );
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
