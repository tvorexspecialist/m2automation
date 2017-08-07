<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\Router\ActionList;
use Magento\Framework\App\RouterInterface;

/**
 * Matches applicaton action in case when robots.txt file was requested
 * @since 2.2.0
 */
class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     * @since 2.2.0
     */
    private $actionFactory;

    /**
     * @var ActionList
     * @since 2.2.0
     */
    private $actionList;

    /**
     * @var ConfigInterface
     * @since 2.2.0
     */
    private $routeConfig;

    /**
     * @param ActionFactory $actionFactory
     * @param ActionList $actionList
     * @param ConfigInterface $routeConfig
     * @since 2.2.0
     */
    public function __construct(
        ActionFactory $actionFactory,
        ActionList $actionList,
        ConfigInterface $routeConfig
    ) {
        $this->actionFactory = $actionFactory;
        $this->actionList = $actionList;
        $this->routeConfig = $routeConfig;
    }

    /**
     * Checks if robots.txt file was requested and returns instance of matched application action class
     *
     * @param RequestInterface $request
     * @return ActionInterface|null
     * @since 2.2.0
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        if ($identifier !== 'robots.txt') {
            return null;
        }

        $modules = $this->routeConfig->getModulesByFrontName('robots');
        if (empty($modules)) {
            return null;
        }

        $actionClassName = $this->actionList->get($modules[0], null, 'index', 'index');
        $actionInstance = $this->actionFactory->create($actionClassName);
        return $actionInstance;
    }
}
