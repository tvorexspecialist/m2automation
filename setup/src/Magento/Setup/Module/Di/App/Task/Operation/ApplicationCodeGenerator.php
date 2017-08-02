<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\Exception\FileSystemException;
use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Code\Scanner\DirectoryScanner;
use Magento\Setup\Module\Di\Code\Scanner\PhpScanner;

/**
 * Class \Magento\Setup\Module\Di\App\Task\Operation\ApplicationCodeGenerator
 *
 * @since 2.0.0
 */
class ApplicationCodeGenerator implements OperationInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $data = [];

    /**
     * @var ClassesScanner
     * @since 2.0.0
     */
    private $classesScanner;

    /**
     * @var PhpScanner
     * @since 2.0.0
     */
    private $phpScanner;

    /**
     * @var DirectoryScanner
     * @since 2.0.0
     */
    private $directoryScanner;

    /**
     * @param ClassesScanner $classesScanner
     * @param PhpScanner $phpScanner
     * @param DirectoryScanner $directoryScanner
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        ClassesScanner $classesScanner,
        PhpScanner $phpScanner,
        DirectoryScanner $directoryScanner,
        $data = []
    ) {
        $this->data = $data;
        $this->classesScanner = $classesScanner;
        $this->phpScanner = $phpScanner;
        $this->directoryScanner = $directoryScanner;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function doOperation()
    {
        if (array_diff(array_keys($this->data), ['filePatterns', 'paths', 'excludePatterns'])
            !== array_diff(['filePatterns', 'paths', 'excludePatterns'], array_keys($this->data))) {
            return;
        }

        foreach ($this->data['paths'] as $paths) {
            if (!is_array($paths)) {
                $paths = (array)$paths;
            }
            $files = [];
            foreach ($paths as $path) {
                $this->classesScanner->getList($path);
                $files = array_merge_recursive(
                    $files,
                    $this->directoryScanner->scan($path, $this->data['filePatterns'], $this->data['excludePatterns'])
                );
            }
            $entities = $this->phpScanner->collectEntities($files['php']);
            foreach ($entities as $entityName) {
                class_exists($entityName);
            }
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
        return 'Application code generator';
    }
}
