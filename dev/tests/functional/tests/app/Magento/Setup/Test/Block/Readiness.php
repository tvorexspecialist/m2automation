<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Readiness block.
 */
class Readiness extends Block
{
    /**
     * 'Start Readiness Check' button.
     *
     * @var string
     */
    protected $readinessCheck = "[ng-click*='state.go']";

    /**
     * 'Next' button.
     *
     * @var string
     */
    protected $next = "[ng-click*='next']";

    /**
     * 'Try Again' button.
     *
     * @var string
     */
    protected $tryAgain = "[ng-click*='forceReload']";

    /**
     * Trash Bin icon.
     *
     * @var string
     */
    protected $removeExtension = '//li//strong[contains(text(), \'%s\')]//..//button';

    /**
     * 'Completed!' message.
     * [ng-switch-when="true"]
     * @var string
     */
    protected $completedMessage = '[ng-switch-when="true"]';

    /**
     * Updater application successful check.
     *
     * @var string
     */
    protected $updaterApplicationCheck = '#updater-application';

    /**
     * Cron script successful check.
     *
     * @var string
     */
    protected $cronScriptCheck = '#cron-script';

    /**
     * Dependency successful check.
     *
     * @var string
     */
    protected $dependencyCheck = '#component-dependency';

    /**
     * PHP Version successful check.
     *
     * @var string
     */
    protected $phpVersionCheck = '#php-version';

    /**
     * PHP Settings successful check.
     *
     * @var string
     */
    protected $phpSettingsCheck = '#php-settings';

    /**
     * PHP Extensions successful check.
     *
     * @var string
     */
    protected $phpExtensionCheck = '#php-extensions';

    /**
     * Click on 'Start Readiness Check' button.
     *
     * @return void
     */
    public function clickReadinessCheck()
    {
        $this->_rootElement->find($this->readinessCheck, Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible($this->completedMessage, Locator::SELECTOR_CSS);
    }

    /**
     * Click on 'Next' button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Click on 'Try Again' button.
     *
     * @return void
     */
    public function clickTryAgain()
    {
        $this->_rootElement->find($this->tryAgain, Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible($this->completedMessage, Locator::SELECTOR_CSS);
    }

    /**
     * Click Trash Bin icon.
     *
     * @param Extension $extension
     * @return void
     */
    public function clickRemoveExtension(Extension $extension)
    {
        $removeExtension = sprintf($this->removeExtension, $extension->getExtensionName());

        $this->_rootElement->find($removeExtension, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get Updater application check result.
     *
     * @return string
     */
    public function getUpdaterApplicationCheck()
    {
        return $this->_rootElement->find($this->updaterApplicationCheck, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get cron script check result.
     *
     * @return string
     */
    public function getCronScriptCheck()
    {
        return $this->_rootElement->find($this->cronScriptCheck, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get dependency check result.
     *
     * @return string
     */
    public function getDependencyCheck()
    {
        return $this->_rootElement->find($this->dependencyCheck, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get PHP Version check result.
     *
     * @return string
     */
    public function getPhpVersionCheck()
    {
        return $this->_rootElement->find($this->phpVersionCheck, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get setting check result.
     *
     * @return string
     */
    public function getSettingsCheck()
    {
        return $this->_rootElement->find($this->phpSettingsCheck, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get PHP Extensions check result.
     *
     * @return string
     */
    public function getPhpExtensionsCheck()
    {
        return $this->_rootElement->find($this->phpExtensionCheck, Locator::SELECTOR_CSS)->getText();
    }
}
