<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import;

class UploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreFileStorageDb;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreFileStorage;

    /**
     * @var \Magento\Framework\Image\AdapterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Writer| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Uploader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uploader;

    protected function setUp()
    {
        $this->coreFileStorageDb = $this->getMockBuilder(\Magento\MediaStorage\Helper\File\Storage\Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreFileStorage = $this->getMockBuilder(\Magento\MediaStorage\Helper\File\Storage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageFactory = $this->getMockBuilder(\Magento\Framework\Image\AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = $this->getMockBuilder(
            \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension::class
        )->disableOriginalConstructor()->getMock();

        $this->readFactory = $this->getMockBuilder(\Magento\Framework\Filesystem\File\ReadFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Writer::class)
            ->setMethods(['writeFile', 'getRelativePath'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryWrite'])
            ->getMock();
        $this->filesystem->expects($this->any())
                        ->method('getDirectoryWrite')
                        ->will($this->returnValue($this->directoryMock));

        $this->uploader = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Uploader::class)
            ->setConstructorArgs([
                $this->coreFileStorageDb,
                $this->coreFileStorage,
                $this->imageFactory,
                $this->validator,
                $this->filesystem,
                $this->readFactory,
            ])
            ->getMock();
    }

    /**
     * @dataProvider moveFileUrlDataProvider
     */
    public function testMoveFileUrl($fileUrl, $expectedHost, $expectedFileName)
    {
        $expectedRelativeFilePath = $this->uploader->getTmpDir() . '/' . $expectedFileName;
        $this->directoryMock->expects($this->any())->method('getRelativePath')->with($expectedRelativeFilePath);
        // Check writeFile() method invoking.
        $this->directoryMock->expects($this->any())->method('writeFile')->will($this->returnValue(null));

        // Create adjusted reader which does not validate path.
        $readMock = $this->getMockBuilder(\Magento\Framework\Filesystem\File\Read::class)
            ->disableOriginalConstructor()
            ->setMethods(['readAll'])
            ->getMock();
        // Check readAll() method invoking.
        $readMock->expects($this->once())->method('readAll')->will($this->returnValue(null));

        $this->readFactory = $this->getMockBuilder(\Magento\Framework\Filesystem\File\ReadFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        // Check create() method invoking with expected argument.
        $this->readFactory->expects($this->once())
                        ->method('create')
                        ->will($this->returnValue($readMock))->with($expectedHost);

        $uploaderMock = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Uploader::class)
            ->setConstructorArgs([
                $this->coreFileStorageDb,
                $this->coreFileStorage,
                $this->imageFactory,
                $this->validator,
                $this->filesystem,
                $this->readFactory,
            ])
            ->setMethods(['_setUploadFile', 'save', 'getTmpDir'])
            ->getMock();

        //Check invoking of getTmpDir(), _setUploadFile(), save() methods.
        $uploaderMock->expects($this->any())->method('getTmpDir')->will($this->returnValue(''));
        $uploaderMock->expects($this->once())->method('_setUploadFile')->will($this->returnSelf());
        $uploaderMock->expects($this->once())->method('save')->will($this->returnValue(['name' => null]));

        $uploaderMock->move($fileUrl);
    }

    public function testMoveFileName()
    {
        $fileName = 'test_uploader_file';
        $expectedRelativeFilePath = $this->uploader->getTmpDir() . '/' . $fileName;
        $this->directoryMock->expects($this->any())->method('getRelativePath')->with($expectedRelativeFilePath);

        $uploaderMock = $this->getMockBuilder(\Magento\CatalogImportExport\Model\Import\Uploader::class)
            ->setConstructorArgs([
                $this->coreFileStorageDb,
                $this->coreFileStorage,
                $this->imageFactory,
                $this->validator,
                $this->filesystem,
                $this->readFactory,
            ])
            ->setMethods(['_setUploadFile', 'save', 'getTmpDir'])
            ->getMock();

        //Check invoking of getTmpDir(), _setUploadFile(), save() methods.
        $uploaderMock->expects($this->once())->method('getTmpDir')->will($this->returnValue(''));
        $uploaderMock->expects($this->once())->method('_setUploadFile')->will($this->returnSelf());
        $uploaderMock->expects($this->once())->method('save')->will($this->returnValue(['name' => null]));

        $uploaderMock->move($fileName);
    }

    public function moveFileUrlDataProvider()
    {
        return [
            [
                '$fileUrl' => 'http://test_uploader_file',
                '$expectedHost' => 'test_uploader_file',
                '$expectedFileName' => 'httptest_uploader_file',
            ],
            [
                '$fileUrl' => 'https://!:^&`;file',
                '$expectedHost' => '!:^&`;file',
                '$expectedFileName' => 'httpsfile',
            ],
        ];
    }
}
