<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;
use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * Class for handling API section within integration.
 *
 * @api
 * @since 2.0.0
 */
class Webapi extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Root ACL Resource
     *
     * @var \Magento\Framework\Acl\RootResource
     * @since 2.0.0
     */
    protected $rootResource;

    /**
     * Acl resource provider
     *
     * @var \Magento\Framework\Acl\AclResource\ProviderInterface
     * @since 2.0.0
     */
    protected $aclResourceProvider;

    /**
     * @var \Magento\Integration\Helper\Data
     * @since 2.0.0
     */
    protected $integrationData;

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     * @since 2.0.0
     */
    protected $integrationService;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Acl\RootResource $rootResource
     * @param \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Acl\RootResource $rootResource,
        \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider,
        \Magento\Integration\Helper\Data $integrationData,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        array $data = []
    ) {
        $this->rootResource = $rootResource;
        $this->aclResourceProvider = $aclResourceProvider;
        $this->integrationData = $integrationData;
        $this->integrationService = $integrationService;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Get tab label
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return __('API');
    }

    /**
     * Get tab title
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Whether tab is available
     *
     * @return bool
     * @since 2.0.0
     */
    public function canShowTab()
    {
        $integrationData = $this->_coreRegistry->registry(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION);
        return !isset(
            $integrationData[Info::DATA_SETUP_TYPE]
        ) || $integrationData[Info::DATA_SETUP_TYPE] != IntegrationModel::TYPE_CONFIG;
    }

    /**
     * Whether tab is visible
     *
     * @return bool
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Class constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $savedFromData = $this->retrieveFormResources();
        if (false !== $savedFromData) {
            $this->setSelectedResources($savedFromData);
            return;
        }
        $integrationData = $this->_coreRegistry->registry(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION);
        if (is_array($integrationData)
            && isset($integrationData['integration_id'])
            && $integrationData['integration_id']
        ) {
            $this->setSelectedResources(
                $this->integrationService->getSelectedResources($integrationData['integration_id'])
            );
        } else {
            $this->setSelectedResources([]);
        }
    }

    /**
     * Retrieve saved resource
     *
     * @return array|bool
     * @since 2.1.0
     */
    protected function retrieveFormResources()
    {
        $savedData = $this->_coreRegistry->registry(
            \Magento\Integration\Controller\Adminhtml\Integration::REGISTRY_KEY_CURRENT_RESOURCE
        );
        if (is_array($savedData)) {
            if ($savedData['all_resources']) {
                return [$this->rootResource->getId()];
            }
            return $savedData['resource'];
        }
        return false;
    }

    /**
     * Check if everything is allowed
     *
     * @return bool
     * @since 2.0.0
     */
    public function isEverythingAllowed()
    {
        return in_array($this->rootResource->getId(), $this->getSelectedResources());
    }

    /**
     * Get Json Representation of Resource Tree
     *
     * @return array
     * @since 2.0.0
     */
    public function getTree()
    {
        return $this->integrationData->mapResources($this->getAclResources());
    }

    /**
     * Get lit of all ACL resources declared in the system.
     *
     * @return array
     * @since 2.2.0
     */
    private function getAclResources()
    {
        $resources = $this->aclResourceProvider->getAclResources();
        $configResource = array_filter(
            $resources,
            function ($node) {
                return $node['id'] == 'Magento_Backend::admin';
            }
        );
        $configResource = reset($configResource);
        return isset($configResource['children']) ? $configResource['children'] : [];
    }
}
