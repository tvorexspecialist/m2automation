<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Config;

class FileSystemReaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  FilesystemReader */
    protected $object;

    protected function setUp()
    {
        $fileResolver = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Framework\Search\Request\Config\FileResolverStub'
        );
        $this->object = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Framework\Search\Request\Config\FilesystemReader',
            ['fileResolver' => $fileResolver]
        );
    }

    public function testRead()
    {
        $result = $this->object->read();
        // Filter values added by \Magento\CatalogSearch\Model\Search\ReaderPlugin
        $result = array_filter($result, function ($k) {
            return in_array($k, ['bool_query', 'filter_query', 'new_match_query']);
        }, ARRAY_FILTER_USE_KEY);
        $expected = include __DIR__ . '/../../_files/search_request_merged.php';
        $this->assertEquals($expected, $result);
    }
}
