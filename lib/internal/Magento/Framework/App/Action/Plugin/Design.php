<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Action\Plugin;

class Design
{
    /**
     * @var \Magento\Framework\View\DesignLoader
     */
    protected $_designLoader;

    /**
     * @param \Magento\Framework\View\DesignLoader $designLoader
     */
    public function __construct(\Magento\Framework\View\DesignLoader $designLoader)
    {
        $this->_designLoader = $designLoader;
    }

    /**
     * Initialize design
     *
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_designLoader->load();
    }
}
