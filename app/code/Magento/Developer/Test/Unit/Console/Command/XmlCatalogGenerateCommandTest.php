<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\XmlCatalogGenerateCommand;
use Symfony\Component\Console\Tester\CommandTester;

class XmlCatalogGenerateCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var XmlCatalogGenerateCommand
     */
    private $command;

    public function testExecuteBadType()
    {
        $fixtureXmlFile = __DIR__ . '/_files/test.xml';

        $filesMock = $this->getMock(\Magento\Framework\App\Utility\Files::class, ['getXmlCatalogFiles'], [], '', false);
        $filesMock->expects($this->at(0))
            ->method('getXmlCatalogFiles')
            ->will($this->returnValue([[$fixtureXmlFile]]));
        $filesMock->expects($this->at(1))
            ->method('getXmlCatalogFiles')
            ->will($this->returnValue([]));
        $urnResolverMock = $this->getMock(\Magento\Framework\Config\Dom\UrnResolver::class, [], [], '', false);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with($this->equalTo('urn:magento:framework:Module/etc/module.xsd'))
            ->will($this->returnValue($fixtureXmlFile));

        $phpstormFormatMock = $this->getMock(
            \Magento\Developer\Model\XmlCatalog\Format\PhpStorm::class,
            [],
            [],
            '',
            false
        );
        $phpstormFormatMock->expects($this->once())
            ->method('generateCatalog')
            ->with(
                $this->equalTo(['urn:magento:framework:Module/etc/module.xsd' => $fixtureXmlFile]),
                $this->equalTo('test')
            )->will($this->returnValue(null));

        $formats = ['phpstorm' => $phpstormFormatMock];
        $readFactory = $this->getMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class, [], [], '', false);
        $readDirMock = $this->getMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class, [], [], '', false);

        $content = file_get_contents($fixtureXmlFile);

        $readDirMock->expects($this->once())
            ->method('readFile')
            ->with($this->equalTo('test.xml'))
            ->will($this->returnValue($content));
        $readFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($readDirMock));

        $this->command = new XmlCatalogGenerateCommand(
            $filesMock,
            $urnResolverMock,
            $readFactory,
            $formats
        );

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([XmlCatalogGenerateCommand::IDE_FILE_PATH_ARGUMENT => 'test']);
        $this->assertEquals('', $commandTester->getDisplay());
    }
}
