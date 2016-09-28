<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config\Writer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Json\JsonInterface;
use Magento\Setup\Module\Di\Compiler\Config\WriterInterface;

class Filesystem implements WriterInterface
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var JsonInterface
     */
    private $json;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     */
    public function __construct(DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
    }

    /**
     * Writes config in storage
     *
     * @param string $key
     * @param array $config
     * @return void
     */
    public function write($key, array $config)
    {
        $this->initialize();

        file_put_contents(
            $this->directoryList->getPath(DirectoryList::DI) . '/' . $key
            . \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled::COMPILED_FILE_EXTENSION,
            $this->getJson()->encode($config)
        );
    }

    /**
     * Initializes writer
     *
     * @return void
     */
    private function initialize()
    {
        if (!file_exists($this->directoryList->getPath(DirectoryList::DI))) {
            mkdir($this->directoryList->getPath(DirectoryList::DI));
        }
    }

    /**
     * Get json encoder/decoder
     *
     * @return JsonInterface
     * @deprecated
     */
    private function getJson()
    {
        if ($this->json === null) {
            $this->json = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(JsonInterface::class);
        }
        return $this->json;
    }
}
