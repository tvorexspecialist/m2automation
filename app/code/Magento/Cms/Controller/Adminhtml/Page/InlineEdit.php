<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Page;

use Magento\Backend\App\Action\Context;
use Magento\Cms\Api\PageRepositoryInterface as PageRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Cms\Api\Data\PageInterface;

class InlineEdit extends \Magento\Backend\App\Action
{
    /** @var PostDataProcessor */
    protected $dataProcessor;

    /** @var PageRepository  */
    protected $pageRepository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param PageRepository $pageRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        PageRepository $pageRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->pageRepository = $pageRepository;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $pageId) {
                    /** @var \Magento\Cms\Model\Page $page */
                    $page = $this->pageRepository->getById($pageId);
                    try {
                        $pageData = $this->dataProcessor->filter($postItems[$pageId]);
                        if (!$this->dataProcessor->validate($pageData)
                            || !$this->dataProcessor->validateRequireEntry($pageData)
                        ) {
                            $error = true;
                            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                                $messages[] = $this->getErrorWithPageId($page, $error->getText());
                            }
                        }
                        $page->setData(array_merge($page->getData(), $pageData));
                        $this->pageRepository->save($page);
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $messages[] = $this->getErrorWithPageId($page, $e->getMessage());
                        $error = true;
                    } catch (\RuntimeException $e) {
                        $messages[] = $this->getErrorWithPageId($page, $e->getMessage());
                        $error = true;
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithPageId(
                            $page,
                            __('Something went wrong while saving the page.')
                        );
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add page title to error message
     *
     * @param PageInterface $page
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithPageId(PageInterface $page, $errorText)
    {
        return '[Page ID: ' . $page->getId() . '] ' . $errorText;
    }
}
