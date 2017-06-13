<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Maintenance;
use \Magento\Setup\Controller\ResponseTypeInterface;

class MaintenanceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * Controller
     *
     * @var \Magento\Setup\Controller\Maintenance
     */
    private $controller;

    public function setUp()
    {
        $this->maintenanceMode = $this->getMock(\Magento\Framework\App\MaintenanceMode::class, [], [], '', false);
        $this->controller = new Maintenance($this->maintenanceMode);

        $request = $this->getMock(\Zend\Http\PhpEnvironment\Request::class, [], [], '', false);
        $response = $this->getMock(\Zend\Http\PhpEnvironment\Response::class, [], [], '', false);
        $routeMatch = $this->getMock(\Zend\Mvc\Router\RouteMatch::class, [], [], '', false);

        $mvcEvent = $this->getMock(\Zend\Mvc\MvcEvent::class, [], [], '', false);
        $mvcEvent->expects($this->any())->method('setRequest')->with($request)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('setResponse')->with($response)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('setTarget')->with($this->controller)->willReturn($mvcEvent);
        $mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);
        $contentArray = '{"disable":false}';
        $request->expects($this->any())->method('getContent')->willReturn($contentArray);

        $this->controller->setEvent($mvcEvent);
        $this->controller->dispatch($request, $response);
    }

    public function testIndexAction()
    {
        $this->maintenanceMode->expects($this->once())->method('set');
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_SUCCESS, $variables['responseType']);
    }

    public function testIndexActionWithExceptions()
    {
        $this->maintenanceMode->expects($this->once())->method('set')->will(
            $this->throwException(new \Exception("Test error message"))
        );
        $jsonModel = $this->controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('responseType', $variables);
        $this->assertEquals(ResponseTypeInterface::RESPONSE_TYPE_ERROR, $variables['responseType']);
        $this->assertArrayHasKey('error', $variables);
        $this->assertEquals("Test error message", $variables['error']);
    }
}
