<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data;

/**
 * Form factory class
 * @since 2.0.0
 */
class FormFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Instance name to create
     *
     * @var string
     * @since 2.0.0
     */
    protected $_instanceName;

    /**
     * Factory construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\Data\Form::class
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create form instance
     *
     * @param array $data
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        /** @var $form \Magento\Framework\Data\Form */
        $form = $this->_objectManager->create($this->_instanceName, $data);
        if (!$form instanceof \Magento\Framework\Data\Form) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(
                    '%1 doesn\'t extend \Magento\Framework\Data\Form',
                    [$this->_instanceName]
                )
            );
        }
        return $form;
    }
}
