<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\View\Asset\PreProcessor;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\PreProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;

/**
 * Class PreprocessorStrategy
 */
class PreprocessorStrategy implements PreProcessorInterface
{
    /**
     * @var FrontendCompilation
     */
    private $frontendCompilation;

    /**
     * @var AlternativeSourceInterface
     */
    private $alternativeSource;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var State
     */
    private $state;

    /**
     * Constructor
     *
     * @param AlternativeSourceInterface $alternativeSource
     * @param FrontendCompilation $frontendCompilation
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        AlternativeSourceInterface $alternativeSource,
        FrontendCompilation $frontendCompilation,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->frontendCompilation = $frontendCompilation;
        $this->alternativeSource = $alternativeSource;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param PreProcessor\Chain $chain
     * @return void
     */
    public function process(PreProcessor\Chain $chain)
    {
        $isClientSideCompilation =
            $this->getAppMode() !== State::MODE_PRODUCTION
            && WorkflowType::CLIENT_SIDE_COMPILATION === $this->scopeConfig->getValue(WorkflowType::CONFIG_NAME_PATH);

        if ($isClientSideCompilation) {
            $this->frontendCompilation->process($chain);
        } else {
            $this->alternativeSource->process($chain);
        }
    }

    /**
     * @return State
     * @deprecated
     */
    private function getState()
    {
        if (null === $this->state) {
            $this->state = ObjectManager::getInstance()->get(ObjectManagerFactory::class)->create(State::class);
        }

        return $this->state;
    }

    /**
     * TODO: Fix this in scope of MAGETWO-54595
     *
     * @return string
     * @deprecated
     */
    private function getAppMode()
    {
        return $this->getState() === State::MODE_DEFAULT
            ? ObjectManager::getInstance()->get(DeploymentConfig::class)->get(State::PARAM_MODE)
            : $this->getState()->getMode();
    }
}
