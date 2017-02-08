<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Block\System\Config;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Analytics form in admin configurations.
 *
 */
class AnalyticsForm extends Form
{
    /**
     * @var string
     */
    private $analyticsStatus = '#analytics_general_enabled';

    /**
     * @var string
     */
    private $analyticsStatusLabel = '#row_analytics_general_label .value';

    /**
     * @var string
     */
    private $submitButton = '#save';

    /**
     * @return array|string
     */
    public function isAnalyticsEnabled()
    {
        return $this->_rootElement->find($this->analyticsStatus, Locator::SELECTOR_CSS)->getValue();
    }

    /**
     * @return array|string
     */
    public function enableAnalytics()
    {
        return $this->_rootElement->find($this->analyticsStatus, Locator::SELECTOR_CSS, 'select')->setValue('Yes');
    }

    /**
     * @return array|string
     */
    public function saveConfig()
    {
        return $this->browser->find($this->submitButton)->click();
    }

    /**
     * @return array|string
     */
    public function getAnalyticsStatus()
    {
        return $this->_rootElement->find($this->analyticsStatusLabel, Locator::SELECTOR_CSS)->getText();
    }
}
