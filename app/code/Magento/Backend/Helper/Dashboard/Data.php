<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Helper\Dashboard;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * Data helper for dashboard
 *
 * @api
 * @since 2.0.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     * @since 2.0.0
     */
    protected $_stores;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_installDate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    private $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param DeploymentConfig $deploymentConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DeploymentConfig $deploymentConfig
    ) {
        parent::__construct(
            $context
        );
        $this->_installDate = $deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE);
        $this->_storeManager = $storeManager;
    }

    /**
     * Retrieve stores configured in system.
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     * @since 2.0.0
     */
    public function getStores()
    {
        if (!$this->_stores) {
            $this->_stores = $this->_storeManager->getStore()->getResourceCollection()->load();
        }
        return $this->_stores;
    }

    /**
     * Retrieve number of loaded stores
     *
     * @return int
     * @since 2.0.0
     */
    public function countStores()
    {
        return sizeof($this->_stores->getItems());
    }

    /**
     * Prepare array with periods for dashboard graphs
     *
     * @return array
     * @since 2.0.0
     */
    public function getDatePeriods()
    {
        return [
            '24h' => __('Last 24 Hours'),
            '7d' => __('Last 7 Days'),
            '1m' => __('Current Month'),
            '1y' => __('YTD'),
            '2y' => __('2YTD')
        ];
    }

    /**
     * Create data hash to ensure that we got valid
     * data and it is not changed by some one else.
     *
     * @param string $data
     * @return string
     * @since 2.0.0
     */
    public function getChartDataHash($data)
    {
        $secret = $this->_installDate;
        return md5($data . $secret);
    }
}
