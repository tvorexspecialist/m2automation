<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Block\System\Config\Form\Fieldset\Modules;

/**
 * Displays a list of <select> tags with names of the modules on tab Stores > Configuration > Advanced / Advanced
 * on the store settings page.
 *
 * @method \Magento\Config\Block\System\Config\Form getForm()
 * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
 */
class DisableOutput extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Framework\DataObject
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     */
    protected $_dummyElement;

    /**
     * @var \Magento\Config\Block\System\Config\Form\Field
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     */
    protected $_fieldRenderer;

    /**
     * @var array
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     */
    protected $_values;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     */
    protected $_moduleList;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->_moduleList = $moduleList;
    }

    /**
     * {@inheritdoc}
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $modules = $this->_moduleList->getNames();

        $dispatchResult = new \Magento\Framework\DataObject($modules);
        $this->_eventManager->dispatch(
            'adminhtml_system_config_advanced_disableoutput_render_before',
            ['modules' => $dispatchResult]
        );
        $modules = $dispatchResult->toArray();

        sort($modules);

        foreach ($modules as $moduleName) {
            if ($moduleName === 'Magento_Backend') {
                continue;
            }
            $html .= $this->_getFieldHtml($element, $moduleName);
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     * @return \Magento\Framework\DataObject
     */
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new \Magento\Framework\DataObject(['showInDefault' => 1, 'showInWebsite' => 1]);
        }
        return $this->_dummyElement;
    }

    /**
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     * @return \Magento\Config\Block\System\Config\Form\Field
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->_layout->getBlockSingleton(
                \Magento\Config\Block\System\Config\Form\Field::class
            );
        }
        return $this->_fieldRenderer;
    }

    /**
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     * @return array
     */
    protected function _getValues()
    {
        if (empty($this->_values)) {
            $this->_values = [
                ['label' => __('Enable'), 'value' => 0],
                ['label' => __('Disable'), 'value' => 1],
            ];
        }
        return $this->_values;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $moduleName
     * @deprecated Magento does not support custom disabling/enabling module output since 2.2.0 version
     * @return mixed
     */
    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $configData = $this->getConfigData();
        $path = 'advanced/modules_disable_output/' . $moduleName;
        //TODO: move as property of form
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = (int)(string)$this->getForm()->getConfigValue($path);
            $inherit = true;
        }

        $element = $this->_getDummyElement();

        $field = $fieldset->addField(
            $moduleName,
            'select',
            [
                'name' => 'groups[modules_disable_output][fields][' . $moduleName . '][value]',
                'label' => $moduleName,
                'value' => $data,
                'values' => $this->_getValues(),
                'inherit' => $inherit,
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($element),
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($element)
            ]
        )->setRenderer(
            $this->_getFieldRenderer()
        );

        return $field->toHtml();
    }
}
