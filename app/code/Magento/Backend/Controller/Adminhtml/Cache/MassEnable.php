<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManager;

/**
 * Controller enables some types of cache
 * @since 2.0.0
 */
class MassEnable extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * @var State
     * @since 2.2.0
     */
    private $state;

    /**
     * Mass action for cache enabling
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        if ($this->getState()->getMode() === State::MODE_PRODUCTION) {
            $this->messageManager->addErrorMessage(__('You can\'t change status of cache type(s) in production mode'));
        } else {
            $this->enableCache();
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('adminhtml/*');
    }

    /**
     * Enable cache
     *
     * @return void
     * @since 2.2.0
     */
    private function enableCache()
    {
        try {
            $types = $this->getRequest()->getParam('types');
            $updatedTypes = 0;
            if (!is_array($types)) {
                $types = [];
            }
            $this->_validateTypes($types);
            foreach ($types as $code) {
                if (!$this->_cacheState->isEnabled($code)) {
                    $this->_cacheState->setEnabled($code, true);
                    $updatedTypes++;
                }
            }
            if ($updatedTypes > 0) {
                $this->_cacheState->persist();
                $this->messageManager->addSuccess(__("%1 cache type(s) enabled.", $updatedTypes));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('An error occurred while enabling cache.'));
        }
    }

    /**
     * Get State Instance
     *
     * @return State
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getState()
    {
        if ($this->state === null) {
            $this->state = ObjectManager::getInstance()->get(State::class);
        }

        return $this->state;
    }
}
