<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Test\Unit;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Code\GeneratedFiles;

class GeneratedFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Magento\Framework\App\Filesystem\DirectoryList | \PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryList;

    /**
     * @var Magento\Framework\Filesystem\Directory\WriteInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $writeInterface;

    /**
     * @var \Magento\Framework\Code\GeneratedFiles
     */
    private $model;

    protected function setUp()
    {
        $this->directoryList = $this->getMock('\Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $writeFactory = $this->getMock('\Magento\Framework\Filesystem\Directory\WriteFactory', [], [], '', false);
        $this->writeInterface = $this->getMock(
            '\Magento\Framework\Filesystem\Directory\WriteInterface',
            [],
            [],
            '',
            false
        );
        $writeFactory->expects($this->once())->method('create')->willReturn($this->writeInterface);
        $this->model = new GeneratedFiles($this->directoryList, $writeFactory);
    }

    /**
     * @param array $getPathMap
     * @param array $isDirectoryMap
     * @param array $deleteMap
     * @dataProvider regenerateDataProvider
     */
    public function testRegenerate($getPathMap, $isDirectoryMap, $deleteMap)
    {

        $this->writeInterface
            ->expects($this->once())
            ->method('isExist')
            ->with(GeneratedFiles::REGENERATE_FLAG)
            ->willReturn(true);
        $this->directoryList->expects($this->exactly(2))->method('getPath')->willReturnMap($getPathMap);
        $this->writeInterface->expects($this->exactly(2))->method('getRelativePath')->willReturnMap($getPathMap);
        $this->writeInterface->expects($this->exactly(2))->method('isDirectory')->willReturnMap($isDirectoryMap);
        $this->writeInterface->expects($this->exactly(1))->method('delete')->willReturnMap($deleteMap);
        $this->model->regenerate();
    }

    /**
     * @return array
     */
    public function regenerateDataProvider()
    {
        $pathToGeneration = 'path/to/generation';
        $pathToDi = 'path/to/di';

        $getPathMap =     [[DirectoryList::GENERATION, $pathToGeneration],
            [DirectoryList::DI, $pathToDi]];
        $deleteMap = [[BP . '/' . $pathToGeneration, true],
            [BP . '/' . $pathToDi, true],
            [BP . GeneratedFiles::REGENERATE_FLAG, true],
        ];

        return [
            'runAll' => [ $getPathMap, [[BP . '/' . $pathToGeneration, true],
                [BP . '/' . $pathToDi, true]], $deleteMap ],
            'noDIfolder' => [ $getPathMap, [[BP . '/' . $pathToGeneration, true],
                [BP . '/' . $pathToDi, false]], $deleteMap],
            'noGenerationfolder' => [$getPathMap, [[BP . '/' . $pathToGeneration, false],
                [BP . '/' . $pathToDi, true]], $deleteMap],
            'nofolders' => [ $getPathMap, [[BP . '/' . $pathToGeneration, false],
                [BP . '/' . $pathToDi, false]], $deleteMap],
        ];
    }

    public function testRegenerateWithNoFlag()
    {
        $this->writeInterface
            ->expects($this->once())
            ->method('isExist')
            ->with(GeneratedFiles::REGENERATE_FLAG)
            ->willReturn(false);
        $this->directoryList->expects($this->never())->method('getPath');
        $this->writeInterface->expects($this->never())->method('getPath');
        $this->writeInterface->expects($this->never())->method('delete');
        $this->model->regenerate();
    }

    public function testRequestRegeneration()
    {
        $this->writeInterface->expects($this->once())->method("touch");
        $this->model->requestRegeneration();
    }
}
