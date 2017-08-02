<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Manage currency symbols block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CurrencySymbol\Block\Adminhtml\System;

/**
 * @api
 * @since 2.0.0
 */
class Currencysymbol extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory
     * @since 2.0.0
     */
    protected $_symbolSystemFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory $symbolSystemFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory $symbolSystemFactory,
        array $data = []
    ) {
        $this->_symbolSystemFactory = $symbolSystemFactory;
        parent::__construct($context, $data);
    }

    /**
     * Constructor. Initialization required variables for class instance.
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_system_currencysymbol';
        parent::_construct();
    }

    /**
     * Custom currency symbol properties
     *
     * @var array
     * @since 2.0.0
     */
    protected $_symbolsData = [];

    /**
     * Prepares layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'save_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Save Currency Symbols'),
                'class' => 'save primary save-currency-symbols',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#currency-symbols-form']],
                ]
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Returns page header
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getHeader()
    {
        return __('Currency Symbols');
    }

    /**
     * Returns URL for save action
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('adminhtml/*/save');
    }

    /**
     * Returns website id
     *
     * @return int
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getWebsiteId()
    {
        return $this->getRequest()->getParam('website');
    }

    /**
     * Returns store id
     *
     * @return int
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getStoreId()
    {
        return $this->getRequest()->getParam('store');
    }

    /**
     * Returns Custom currency symbol properties
     *
     * @return array
     * @since 2.0.0
     */
    public function getCurrencySymbolsData()
    {
        if (!$this->_symbolsData) {
            $this->_symbolsData = $this->_symbolSystemFactory->create()->getCurrencySymbolsData();
        }
        return $this->_symbolsData;
    }

    /**
     * Returns inheritance text
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getInheritText()
    {
        return __('Use Standard');
    }
}
