<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Main controller of the Setup Wizard
 * @since 2.0.0
 */
class Index extends AbstractActionController
{
    /**
     * @return ViewModel|\Zend\Http\Response
     * @since 2.0.0
     */
    public function indexAction()
    {
        return new ViewModel();
    }
}
