<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\PackagesData;

/**
 * Controller for extensions grid tasks
 */
class InstallExtensionGrid extends AbstractActionController
{
    /**
     * @var PackagesData
     */
    private $packagesData;

    /**
     * @param PackagesData $packagesData
     */
    public function __construct(PackagesData $packagesData)
    {
        $this->packagesData = $packagesData;
    }

    /**
     * Index page action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Get Extensions info action
     *
     * @return JsonModel
     */
    public function extensionsAction()
    {
        $extensions = $this->packagesData->getPackagesForInstall();
        $packages = isset($extensions['packages']) ? $extensions['packages'] : [];
        return new JsonModel(
            [
                'success' => true,
                'extensions' => array_values($packages),
                'total' => count($packages)
            ]
        );
    }
}
