<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\FixtureModel;

class FixtureModelTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Setup\Fixtures\FixtureModel
     */
    private $model;

    public function setUp()
    {
        $reindexCommandMock = $this->getMock(
            \Magento\Indexer\Console\Command\IndexerReindexCommand::class,
            [],
            [],
            '',
            false
        );
        $fileParserMock = $this->getMock(\Magento\Framework\Xml\Parser::class, [], [], '', false);

        $this->model = new FixtureModel($reindexCommandMock, $fileParserMock);
    }

    public function testReindex()
    {
        $outputMock = $this->getMock(\Symfony\Component\Console\Output\OutputInterface::class, [], [], '', false);
        $this->model->reindex($outputMock);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Profile configuration file `exception.file` is not readable or does not exists.
     */
    public function testLoadConfigException()
    {
        $this->model->loadConfig('exception.file');
    }

    public function testLoadConfig()
    {
        $reindexCommandMock = $this->getMock(
            \Magento\Indexer\Console\Command\IndexerReindexCommand::class,
            [],
            [],
            '',
            false
        );

        $fileParserMock = $this->getMock(\Magento\Framework\Xml\Parser::class, ['getDom', 'xmlToArray'], [], '', false);
        $fileParserMock->expects($this->exactly(2))->method('xmlToArray')->willReturn(
            ['config' => [ 'profile' => ['some_key' => 'some_value']]]
        );

        $domMock = $this->getMock(\DOMDocument::class, ['load', 'xinclude'], [], '', false);
        $domMock->expects($this->once())->method('load')->with('config.file')->willReturn(
            $fileParserMock->xmlToArray()
        );
        $domMock->expects($this->once())->method('xinclude');

        $fileParserMock->expects($this->exactly(2))->method('getDom')->willReturn($domMock);

        $this->model = new FixtureModel($reindexCommandMock, $fileParserMock);
        $this->model->loadConfig('config.file');
        $this->assertSame('some_value', $this->model->getValue('some_key'));
    }

    public function testGetValue()
    {
        $this->assertSame(null, $this->model->getValue('null_key'));
    }
}

namespace Magento\Setup\Fixtures;

/**
 * Overriding the built-in PHP function since it cannot be mocked->
 *
 * The method is used in FixtureModel. loadConfig in an if statement. By overriding this method we are able to test
 * both of the possible cases based on the return value of is_readable.
 *
 * @param string $filename
 * @return bool
 */
function is_readable($filename)
{
    if (strpos($filename, 'exception') !== false) {
        return false;
    }
    return true;
}
