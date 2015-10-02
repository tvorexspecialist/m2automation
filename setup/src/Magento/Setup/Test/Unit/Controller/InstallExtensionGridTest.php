<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\InstallExtensionGrid;

class InstallExtensionGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\InstallExtensionGrid
     */
    private $controller;

    /**
     * @var \Magento\Setup\Model\ConnectManager
     */
    private $connectManager;

    public function setUp()
    {
        $this->connectManager = $this->getMock('Magento\Setup\Model\ConnectManager', ['getPackagesForInstall'], [], '', false);
        $this->controller = new InstallExtensionGrid($this->connectManager);
    }

    /**
     * @covers \Magento\Setup\Controller\InstallExtensionGrid::indexAction
     */
    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);
    }

    /**
     * @param array $extensions
     * @dataProvider dataProviderForTestExtensionsAction
     * @covers \Magento\Setup\Controller\InstallExtensionGrid::extensionsAction
     */
    public function testExtensionsAction($extensions)
    {
        $this->connectManager
            ->expects($this->once())
            ->method('getPackagesForInstall')
            ->will($this->returnValue($extensions));
        $jsonModel = $this->controller->extensionsAction();
        $this->assertInstanceOf('\Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertArrayHasKey('extensions', $variables);
        $this->assertArrayHasKey('total', $variables);
        $this->assertTrue($variables['success']);
    }

    /**
     * @return array
     */
    public function dataProviderForTestExtensionsAction()
    {
        $extensions = array(
            'magento/testing-extension' => array (
                'name' => 'magento/testing-extension',
                'type' => 'module',
                'version' => '2.2.2',
                'author' => 'magento'),
            'magento/my-first-module' => array (
                'name' => 'magento/my-first-module',
                'type' => 'module',
                'version' => '2.0.0',
                'author' => 'magento'),
            'magento/last-extension' => array (
                'name' => 'magento/last-extension',
                'type' => 'module',
                'version' => '2.1.1',
                'author' => 'magento'),
            'magento/magento-second-module' => array (
                'name' => 'magento/magento-second-module',
                'type' => 'module',
                'version' => '2.0.0',
                'author' => 'magento'));
        return array(
            array($extensions)
        );
    }
}