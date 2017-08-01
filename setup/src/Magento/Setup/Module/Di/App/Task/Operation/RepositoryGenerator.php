<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;

/**
 * Class \Magento\Setup\Module\Di\App\Task\Operation\RepositoryGenerator
 *
 * @since 2.0.0
 */
class RepositoryGenerator implements OperationInterface
{
    /**
     * @var Scanner\RepositoryScanner
     * @since 2.0.0
     */
    private $repositoryScanner;

    /**
     * @var array
     * @since 2.0.0
     */
    private $data;

    /**
     * @var ClassesScanner
     * @since 2.0.0
     */
    private $classesScanner;

    /**
     * @var Scanner\ConfigurationScanner
     * @since 2.1.0
     */
    private $configurationScanner;

    /**
     * @param ClassesScanner $classesScanner
     * @param Scanner\RepositoryScanner $repositoryScanner
     * @param Scanner\ConfigurationScanner $configurationScanner
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        ClassesScanner $classesScanner,
        Scanner\RepositoryScanner $repositoryScanner,
        \Magento\Setup\Module\Di\Code\Scanner\ConfigurationScanner $configurationScanner,
        $data = []
    ) {
        $this->repositoryScanner = $repositoryScanner;
        $this->data = $data;
        $this->classesScanner = $classesScanner;
        $this->configurationScanner = $configurationScanner;
    }

    /**
     * Processes operation task
     *
     * @return void
     * @since 2.0.0
     */
    public function doOperation()
    {
        foreach ($this->data['paths'] as $path) {
            $this->classesScanner->getList($path);
        }
        $this->repositoryScanner->setUseAutoload(false);
        $files = $this->configurationScanner->scan('di.xml');
        $repositories = $this->repositoryScanner->collectEntities($files);
        foreach ($repositories as $entityName) {
            class_exists($entityName);
        }
    }

    /**
     * Returns operation name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return 'Repositories code generation';
    }
}
