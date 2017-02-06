<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Category\Attribute\Backend;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    private $attribute;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    private $imageUploader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->attribute = $this->getMockForAbstractClass(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            [],
            'TestAttribute',
            false,
            false,
            true,
            ['getName']
        );

        $this->attribute->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('test_attribute'));

        $this->logger = $this->getMockForAbstractClass(
            \Psr\Log\LoggerInterface::class,
            [],
            'TestLogger',
            false,
            false,
            true,
            ['critical']
        );

        $this->imageUploader = $this->getMock(
            \Magento\Catalog\Model\ImageUploader::class,
            ['moveFileFromTmp'],
            [],
            '',
            false
        );
    }

    /**
     * @return array
     */
    public function deletedValueDataProvider()
    {
        return [
            [false],
            [['delete' => true]]
        ];
    }

    /**
     * @dataProvider deletedValueDataProvider
     *
     * @param array $value
     */
    public function testBeforeSaveValueDeletion($value)
    {
        $model = $this->objectManager->getObject(\Magento\Catalog\Model\Category\Attribute\Backend\Image::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => $value
        ]);

        $model->beforeSave($object);

        $this->assertEquals('', $object->getTestAttribute());
    }

    /**
     * @return array
     */
    public function invalidValueDataProvider()
    {
        $closure = function () {
            return false;
        };

        return [
            [1234],
            [true],
            [new \stdClass()],
            [$closure],
            [['a' => 1, 'b' => 2]]
        ];
    }

    /**
     * @dataProvider invalidValueDataProvider
     *
     * @param array $value
     */
    public function testBeforeSaveValueInvalid($value)
    {
        $model = $this->objectManager->getObject(\Magento\Catalog\Model\Category\Attribute\Backend\Image::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => $value
        ]);

        $model->beforeSave($object);

        $this->assertEquals('', $object->getTestAttribute());
    }

    public function testBeforeSaveAttributeFileName()
    {
        $model = $this->objectManager->getObject(\Magento\Catalog\Model\Category\Attribute\Backend\Image::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => [
                ['name' => 'test123.jpg']
            ]
        ]);

        $model->beforeSave($object);

        $this->assertEquals('test123.jpg', $object->getTestAttribute());
    }

    public function testBeforeSaveTemporaryAttribute()
    {
        $model = $this->objectManager->getObject(\Magento\Catalog\Model\Category\Attribute\Backend\Image::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => [
                ['name' => 'test123.jpg', 'tmp_name' => 'abc123', 'url' => 'http://www.example.com/test123.jpg']
            ]
        ]);

        $model->beforeSave($object);

        $this->assertEquals([
            ['name' => 'test123.jpg', 'tmp_name' => 'abc123', 'url' => 'http://www.example.com/test123.jpg']
        ], $object->getData('_additional_data_test_attribute'));
    }

    public function testBeforeSaveAttributeStringValue()
    {
        $model = $this->objectManager->getObject(\Magento\Catalog\Model\Category\Attribute\Backend\Image::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => 'test123.jpg'
        ]);

        $model->beforeSave($object);

        $this->assertEquals('test123.jpg', $object->getTestAttribute());
        $this->assertNull($object->getData('_additional_data_test_attribute'));
    }

    /**
     * @return \Magento\Catalog\Model\Category\Attribute\Backend\Image
     */
    private function setUpModelForAfterSave()
    {
        $objectManagerMock = $this->getMock(
            \Magento\Framework\App\ObjectManager::class,
            ['get'],
            [],
            '',
            false
        );

        $imageUploaderMock = $this->imageUploader;

        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($class, $params = []) use ($imageUploaderMock) {
                if ($class == \Magento\Catalog\CategoryImageUpload::class) {
                    return $imageUploaderMock;
                }

                return $this->objectManager->get($class, $params);
            }));

        $model = $this->objectManager->getObject(\Magento\Catalog\Model\Category\Attribute\Backend\Image::class, [
            'objectManager' => $objectManagerMock,
            'logger' => $this->logger
        ]);
        $this->objectManager->setBackwardCompatibleProperty($model, 'imageUploader', $this->imageUploader);

        return $model->setAttribute($this->attribute);
    }

    public function attributeValueDataProvider()
    {
        return [
            [[['name' => 'test1234.jpg']]],
            ['test1234.jpg'],
            [''],
            [false]
        ];
    }

    /**
     * @dataProvider attributeValueDataProvider
     *
     * @param array $value
     */
    public function testAfterSaveWithAdditionalData($value)
    {
        $model = $this->setUpModelForAfterSave();

        $this->imageUploader->expects($this->once())
            ->method('moveFileFromTmp')
            ->with($this->equalTo('test1234.jpg'));

        $object = new \Magento\Framework\DataObject(
            [
                'test_attribute' => $value,
                '_additional_data_test_attribute' => [['name' => 'test1234.jpg']]
            ]
        );

        $model->afterSave($object);
    }

    /**
     * @dataProvider attributeValueDataProvider
     *
     * @param array $value
     */
    public function testAfterSaveWithoutAdditionalData($value)
    {
        $model = $this->setUpModelForAfterSave();

        $this->imageUploader->expects($this->never())
            ->method('moveFileFromTmp');

        $object = new \Magento\Framework\DataObject(
            [
                'test_attribute' => $value
            ]
        );

        $model->afterSave($object);
    }

    public function testAfterSaveWithExceptions()
    {
        $model = $this->setUpModelForAfterSave();

        $exception = new \Exception();

        $this->imageUploader->expects($this->any())
            ->method('moveFileFromTmp')
            ->will($this->throwException($exception));

        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo($exception));

        $object = new \Magento\Framework\DataObject(
            [
                '_additional_data_test_attribute' => [['name' => 'test1234.jpg']]
            ]
        );

        $model->afterSave($object);
    }
}
