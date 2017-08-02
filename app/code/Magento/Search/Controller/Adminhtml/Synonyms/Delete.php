<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Controller\Adminhtml\Synonyms;

/**
 * Delete Controller
 * @since 2.1.0
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * @var \Psr\Log\LoggerInterface $logger
     * @since 2.1.0
     */
    private $logger;

    /**
     * @var \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     * @since 2.1.0
     */
    private $synGroupRepository;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Search\Api\SynonymGroupRepositoryInterface $synGroupRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->synGroupRepository = $synGroupRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.1.0
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('group_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                /** @var \Magento\Search\Model\SynonymGroup $synGroupModel */
                $synGroupModel = $this->synGroupRepository->get($id);
                $this->synGroupRepository->delete($synGroupModel);
                $this->messageManager->addSuccess(__('The synonym group has been deleted.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->logger->error($e);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('An error was encountered while performing delete operation.'));
                $this->logger->error($e);
            }
        } else {
            $this->messageManager->addError(__('We can\'t find a synonym group to delete.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
