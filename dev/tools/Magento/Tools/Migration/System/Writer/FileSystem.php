<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Writer;

use Magento\Framework\Filesystem\DriverInterface;

class FileSystem implements \Magento\Tools\Migration\System\WriterInterface
{
    /**
     * @param string $fileName
     * @param string $contents
     * @return void
     */
    public function write($fileName, $contents)
    {
        if (false == is_dir(dirname($fileName))) {
            mkdir(dirname($fileName), DriverInterface::WRITEABLE_DIRECTORY_MODE, true);
        }
        file_put_contents($fileName, $contents);
    }

    /**
     * Remove file
     *
     * @param string $fileName
     * @return void
     */
    public function remove($fileName)
    {
        unlink($fileName);
    }
}
