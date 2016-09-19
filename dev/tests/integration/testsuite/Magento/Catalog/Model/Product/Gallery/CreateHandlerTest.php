<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

/**
 * Test class for \Magento\Catalog\Model\Product\Gallery\CreateHandler.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDataFixture Magento/Catalog/_files/product_image.php
 */
class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Gallery\CreateHandler
     */
    protected $createHandler;

    private $fileName = '/m/a/magento_image.jpg';

    private $fileLabel = 'Magento image';

    protected function setUp()
    {
        $this->createHandler = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Gallery\CreateHandler::class
        );
    }

    /**
     * @covers \Magento\Catalog\Model\Product\Gallery\CreateHandler::execute
     */
    public function testExecuteWithImageDuplicate()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load(1);
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['file' => $this->fileName, 'label' => $this->fileLabel]]]
        );
        $product->setData('image', $this->fileName);
        $this->createHandler->execute($product);
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('media_gallery/images/image/new_file'));
        $this->assertEquals($this->fileLabel, $product->getData('image_label'));

        $product->setIsDuplicate(true);
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['value_id' => '100', 'file' => $this->fileName, 'label' => $this->fileLabel]]]
        );
        $this->createHandler->execute($product);
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('media_gallery/duplicate/100'));
        $this->assertEquals($this->fileLabel, $product->getData('image_label'));
    }

    /**
     * @covers \Magento\Catalog\Model\Product\Gallery\CreateHandler::execute
     * @dataProvider executeDataProvider
     * @param array $expectedValues
     */
    public function testExecuteWithImageRoles($expectedValues)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load(1);
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['file' => $this->fileName, 'label' => '']]]
        );
        foreach ($expectedValues as $mediaAttribute => $value) {
            if ($value) {
                $product->setData($mediaAttribute, $value);
            }
        }
        $this->createHandler->execute($product);
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('media_gallery/images/image/new_file'));
        $resource = $product->getResource();
        $attributeValues = [];
        foreach (array_keys($expectedValues) as $mediaAttribute) {
            $attributeValues[$mediaAttribute] = $resource->getAttributeRawValue(
                $product->getId(),
                $resource->getAttribute($mediaAttribute),
                $product->getStoreId()
            );
        }
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('media_gallery/images/image/new_file'));
        $this->assertEquals($expectedValues, $attributeValues);
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                [
                    'image' => $this->fileName,
                    'small_image' => $this->fileName,
                    'swatch_image' => $this->fileName,
                    'thumbnail' => $this->fileName
                ]
            ],
            [
                [
                    'image' => 'no_selection',
                    'small_image' => 'no_selection',
                    'swatch_image' => 'no_selection',
                    'thumbnail' => 'no_selection'
                ]
            ],
            [
                [
                    'image' => null,
                    'small_image' => null,
                    'swatch_image' => null,
                    'thumbnail' => null
                ]
            ]
        ];
    }
}
