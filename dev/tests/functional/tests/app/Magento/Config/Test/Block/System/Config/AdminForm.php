<?php

namespace Magento\Config\Test\Block\System\Config;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Admin Security edit form in admin.
 */
/* this class needs to be created becuase we need to check for the availability of Admin account sharing settings, This is not possible using the form block class only
*/
class AdminForm extends Form
{
    protected $AdminAccountSharingField = "#admin_security_admin_account_sharing";

    public function AdminAccountSharingAvailability() {
        return $this->_rootElement->find($this->AdminAccountSharingField, Locator::SELECTOR_CSS)->isVisible();
    }
}
