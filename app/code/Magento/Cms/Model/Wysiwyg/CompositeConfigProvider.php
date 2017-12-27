<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Model\Wysiwyg;

/**
 * Class CompositeConfigProvider loads required config by adapter specified in system configuration
 * General > Content Management >WYSIWYG Options > WYSIWYG Editor
 */
class CompositeConfigProvider
{
    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    private $activeEditor;

    /**
     * List of variable config processors by adapter type
     *
     * @var array
     */
    private $variablePluginConfigProvider;

    /**
     * List of widget config processors by adapter type
     *
     * @var array
     */
    private $widgetPluginConfigProvider;

    /**
     * List of wysiwyg config postprocessors by adapter type
     *
     * @var array
     */
    private $wysiwygConfigPostProcessor;

    /**
     * Factory to create required processor object
     *
     * @var \Magento\Cms\Model\Wysiwyg\ConfigProviderFactory
     */
    private $configProviderFactory;

    /**
     * Current active editor path
     *
     * @var string
     */
    private $activeEditorPath;

    /**
     * @param \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
     * @param ConfigProviderFactory $configProviderFactory
     * @param array $variablePluginConfigPovider
     * @param array $widgetPluginConfigProvider
     * @param array $wysiwygConfigPostProcessor
     */
    public function __construct(
        \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor,
        \Magento\Cms\Model\Wysiwyg\ConfigProviderFactory $configProviderFactory,
        array $variablePluginConfigPovider,
        array $widgetPluginConfigProvider,
        array $wysiwygConfigPostProcessor
    ) {
        $this->activeEditor = $activeEditor;
        $this->configProviderFactory = $configProviderFactory;
        $this->variablePluginConfigProvider = $variablePluginConfigPovider;
        $this->widgetPluginConfigProvider = $widgetPluginConfigProvider;
        $this->wysiwygConfigPostProcessor = $wysiwygConfigPostProcessor;
    }

    /**
     * Add config for variable plugin
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function processVariableConfig($config)
    {
        return $this->updateConfig($config, $this->variablePluginConfigProvider);
    }

    /**
     * Add config for widget plugin
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function processWidgetConfig($config)
    {
        return $this->updateConfig($config, $this->widgetPluginConfigProvider);
    }

    /**
     * Update wysiwyg config with data required for adapter
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function processWysiswygConfig($config)
    {
        return $this->updateConfig($config, $this->wysiwygConfigPostProcessor);
    }

    /**
     * Returns active editor path
     *
     * @return string
     */
    private function getActiveEditorPath()
    {
        if (!isset($this->activeEditorPath)) {
            $this->activeEditorPath = $this->activeEditor->getWysiwygAdapterPath();
        }
        return $this->activeEditorPath;
    }

    /**
     * Update config using config provider by active editor path
     *
     * @param \Magento\Framework\DataObject $config
     * @param array $configProviders
     * @return \Magento\Framework\DataObject
     */
    private function updateConfig($config, array $configProviders)
    {
        $adapterType = $this->getActiveEditorPath();
        //Extension point to update plugin settings by adapter type
        $providerClass = isset($configProviders[$adapterType])
            ? $configProviders[$adapterType]
            : $configProviders['default'];
        /** @var \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface $provider */
        $provider = $this->configProviderFactory->create($providerClass);
        return $provider->getConfig($config);
    }
}
