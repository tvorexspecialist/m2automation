<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model;

use Magento\Framework\Module\Dir;

/**
 * Configuration for reports
 * @api
 * @since 2.0.0
 */
class Config extends \Magento\Framework\DataObject
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     * @since 2.0.0
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_moduleReader = $moduleReader;
        $this->_storeManager = $storeManager;
    }

    /**
     * Return reports global configuration
     *
     * @return string
     * @since 2.0.0
     */
    public function getGlobalConfig()
    {
        $dom = new \DOMDocument();
        $dom->load($this->_moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Reports') . '/flexConfig.xml');

        $baseUrl = $dom->createElement('baseUrl');
        $baseUrl->nodeValue = $this->_storeManager->getBaseUrl();

        $dom->documentElement->appendChild($baseUrl);

        return $dom->saveXML();
    }

    /**
     * Return reports language
     *
     * @return string
     * @since 2.0.0
     */
    public function getLanguage()
    {
        return file_get_contents(
            $this->_moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Reports') . '/flexLanguage.xml'
        );
    }

    /**
     * Return reports dashboard
     *
     * @return string
     * @since 2.0.0
     */
    public function getDashboard()
    {
        return file_get_contents(
            $this->_moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Reports') . '/flexDashboard.xml'
        );
    }
}
