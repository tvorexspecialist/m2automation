<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Controller\Adminhtml\System;

use Magento\Framework\Exception\LocalizedException;

/**
 * System Configuration Abstract Controller
 * @api
 * @since 2.0.0
 */
abstract class AbstractConfig extends \Magento\Backend\App\AbstractAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Config::config';

    /**
     * @var \Magento\Config\Model\Config\Structure
     * @since 2.0.0
     */
    protected $_configStructure;

    /**
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $_sectionChecker;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param mixed $sectionChecker - deprecated
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        $sectionChecker
    ) {
        parent::__construct($context);
        $this->_configStructure = $configStructure;
        $this->_sectionChecker = $sectionChecker;
    }

    /**
     * Check if current section is found and is allowed
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @since 2.0.0
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$request->getParam('section')) {
            try {
                $request->setParam('section', $this->_configStructure->getFirstSection()->getId());
            } catch (LocalizedException $e) {
                /** If visible section not found need to show only config index page without sections if it allow. */
                $this->messageManager->addWarningMessage($e->getMessage());
            }
        }
        return parent::dispatch($request);
    }

    /**
     * Check is allow modify system configuration
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _isAllowed()
    {
        $sectionId = $this->_request->getParam('section');
        return parent::_isAllowed()
            || $this->_configStructure->getElement($sectionId)->isAllowed();
    }

    /**
     * Save state of configuration field sets
     *
     * @param array $configState
     * @return bool
     * @since 2.0.0
     */
    protected function _saveState($configState = [])
    {
        if (is_array($configState)) {
            $configState = $this->sanitizeConfigState($configState);
            $adminUser = $this->_auth->getUser();
            $extra = $adminUser->getExtra();
            if (!is_array($extra)) {
                $extra = [];
            }
            if (!isset($extra['configState'])) {
                $extra['configState'] = [];
            }
            foreach ($configState as $fieldset => $state) {
                $extra['configState'][$fieldset] = $state;
            }
            $adminUser->saveExtra($extra);
        }
        return true;
    }

    /**
     * Sanitize config state data
     *
     * @param array $configState
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.1.0
     */
    protected function sanitizeConfigState($configState)
    {
        $sectionList = $this->_configStructure->getSectionList();
        $sanitizedConfigState = $configState;
        foreach ($configState as $sectionId => $value) {
            if (array_key_exists($sectionId, $sectionList)) {
                $sanitizedConfigState[$sectionId] = (bool)$sanitizedConfigState[$sectionId] ? '1' : '0';
            } else {
                unset($sanitizedConfigState[$sectionId]);
            }
        }
        return $sanitizedConfigState;
    }
}
