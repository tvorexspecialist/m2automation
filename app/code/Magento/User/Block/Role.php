<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block;

/**
 * Magento_User role block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Role extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_controller = 'user_role';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_blockGroup = 'Magento_User';

    /**
     * Class constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_headerText = __('Roles');
        $this->_addButtonLabel = __('Add New Role');
        parent::_construct();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/editrole');
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        if (!$this->getLayout()->getChildName($this->getNameInLayout(), 'grid')) {
            $this->setChild(
                'grid',
                $this->getLayout()->createBlock(
                    $this->_blockGroup . '\\Block\\Role\\Grid',
                    $this->_controller . '.grid'
                )->setSaveParametersInSession(
                    true
                )
            );
        }
        return \Magento\Backend\Block\Widget\Container::_prepareLayout();
    }

    /**
     * Prepare output HTML
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('permissions_role_html_before', ['block' => $this]);
        return parent::_toHtml();
    }
}
