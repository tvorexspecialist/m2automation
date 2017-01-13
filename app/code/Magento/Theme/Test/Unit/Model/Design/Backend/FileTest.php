<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Framework\UrlInterface;
use Magento\Theme\Model\Design\Backend\File;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $mediaDirectory;

    /** @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    /** @var File */
    protected $fileBackend;

    /**
     * @var \Magento\Framework\File\Mime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mime;

    public function setUp()
    {
        $context = $this->getMockObject(\Magento\Framework\Model\Context::class);
        $registry = $this->getMockObject(\Magento\Framework\Registry::class);
        $config = $this->getMockObjectForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $cacheTypeList = $this->getMockObjectForAbstractClass(\Magento\Framework\App\Cache\TypeListInterface::class);
        $uploaderFactory = $this->getMockObject(\Magento\MediaStorage\Model\File\UploaderFactory::class, ['create']);
        $requestData = $this->getMockObjectForAbstractClass(
            \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface::class
        );
        $filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaDirectory = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
            ->getMockForAbstractClass();

        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();

        $this->mime = $this->getMockBuilder(\Magento\Framework\File\Mime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileBackend = new File(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $this->urlBuilder
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty(
            $this->fileBackend,
            'mime',
            $this->mime
        );
    }

    public function tearDown()
    {
        unset($this->fileBackend);
    }

    /**
     * @param string $class
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockObject($class, $methods = [])
    {
        $builder =  $this->getMockBuilder($class)
            ->disableOriginalConstructor();
        if (count($methods)) {
            $builder->setMethods($methods);
        }
        return  $builder->getMock();
    }

    /**
     * @param string $class
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockObjectForAbstractClass($class)
    {
        return  $this->getMockBuilder($class)
            ->getMockForAbstractClass();
    }

    public function testAfterLoad()
    {
        $value = 'filename.jpg';
        $mime = 'image/jpg';

        $absoluteFilePath = '/absolute_path/' . $value;

        $this->fileBackend->setValue($value);
        $this->fileBackend->setFieldConfig(
            [
                'upload_dir' => [
                    'value' => 'value',
                    'config' => 'system/filesystem/media',
                ],
                'base_url' => [
                    'type' => 'media',
                    'value' => 'design/file'
                ],
            ]
        );

        $this->mediaDirectory->expects($this->once())
            ->method('isExist')
            ->with('value/' . $value)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with('value/' . $value)
            ->willReturn($absoluteFilePath);

        $this->urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->with(['_type' => UrlInterface::URL_TYPE_MEDIA])
            ->willReturn('http://magento2.com/pub/media/');
        $this->mediaDirectory->expects($this->once())
            ->method('getRelativePath')
            ->with('value')
            ->willReturn('value');
        $this->mediaDirectory->expects($this->once())
            ->method('stat')
            ->with('value/' . $value)
            ->willReturn(['size' => 234234]);

        $this->mime->expects($this->once())
            ->method('getMimeType')
            ->with($absoluteFilePath)
            ->willReturn($mime);

        $this->fileBackend->afterLoad();
        $this->assertEquals(
            [
                [
                    'url' => 'http://magento2.com/pub/media/design/file/' . $value,
                    'file' => $value,
                    'size' => 234234,
                    'exists' => true,
                    'name' => $value,
                    'type' => $mime,
                ]
            ],
            $this->fileBackend->getValue()
        );
    }

    public function testBeforeSave()
    {
        $value = 'filename.jpg';
        $tmpMediaPath = 'tmp/design/file/' . $value;
        $this->fileBackend->setScope('store');
        $this->fileBackend->setScopeId(1);
        $this->fileBackend->setValue(
            [
                [
                    'url' => 'http://magento2.com/pub/media/tmp/image/' . $value,
                    'file' => $value,
                    'size' => 234234,
                ]
            ]
        );
        $this->fileBackend->setFieldConfig(
            [
                'upload_dir' => [
                    'value' => 'value',
                    'config' => 'system/filesystem/media',
                ],
            ]
        );

        $this->mediaDirectory->expects($this->once())
            ->method('copyFile')
            ->with($tmpMediaPath, '/' . $value)
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->with($tmpMediaPath);

        $this->fileBackend->beforeSave();
        $this->assertEquals('filename.jpg', $this->fileBackend->getValue());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage header_logo_src does not contain field 'file'
     */
    public function testBeforeSaveWithoutFile()
    {
        $this->fileBackend->setData(
            [
                'value' => [
                    'test' => ''
                ],
                'field_config' => [
                    'field' => 'header_logo_src'
                ],
            ]
        );
        $this->fileBackend->beforeSave();
    }

    public function testBeforeSaveWithExistingFile()
    {
        $value = 'filename.jpg';
        $this->fileBackend->setValue(
            [
                [
                    'url' => 'http://magento2.com/pub/media/tmp/image/' . $value,
                    'file' => $value,
                    'size' => 234234,
                    'exists' => true
                ]
            ]
        );
        $this->fileBackend->beforeSave();
        $this->assertEquals(
            $value,
            $this->fileBackend->getValue()
        );
    }
}
