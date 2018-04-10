<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Helper\Wysiwyg;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\ObjectManager;

class ImagesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testGetStorageRoot()
    {
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        );
        $mediaPath = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $helper = $this->generateHelper();
        $this->assertStringStartsWith($mediaPath, $helper->getStorageRoot());
    }

    public function testGetCurrentUrl()
    {
        $helper = $this->generateHelper();
        $this->assertStringStartsWith('http://localhost/', $helper->getCurrentUrl());
    }

    /**
     * @param bool $staticUrlsAllowed
     * @param string $filename
     * @param bool $renderAsTag
     * @param string|callable $expectedResult - string or callable to make unique assertions on $expectedResult
     *
     * @magentoConfigFixture current_store web/unsecure/base_url http://example.com/
     * @dataProvider providerGetImageHtmlDeclaration
     */
    public function testGetImageHtmlDeclaration(
        $staticUrlsAllowed,
        $filename,
        $renderAsTag,
        $expectedResult
    ) {
        $helper = $this->generateHelper($staticUrlsAllowed);

        $actualResult = $helper->getImageHtmlDeclaration($filename, $renderAsTag);

        if (is_callable($expectedResult)) {
            $expectedResult($actualResult);
        } else {
            $this->assertEquals(
                $expectedResult,
                $actualResult
            );
        }
    }

    /**
     * Data provider for testGetImageHtmlDeclaration
     *
     * @return array
     */
    public function providerGetImageHtmlDeclaration()
    {
        return [
            [true, 'wysiwyg/hello.png', true, '<img src="http://example.com/pub/media/wysiwyg/hello.png" alt="" />'],
            [
                false,
                'wysiwyg/hello.png',
                false,
                function ($actualResult) {
                    $expectedResult = (
                        '/backend/cms/wysiwyg/directive/___directive/' .
                        'e3ttZWRpYSB1cmw9Ind5c2l3eWcvaGVsbG8ucG5nIn19/'
                    );

                    $this->assertEquals($expectedResult, parse_url($actualResult, PHP_URL_PATH));
                }
            ],
            [true, 'wysiwyg/hello.png', false, 'http://example.com/pub/media/wysiwyg/hello.png'],
            [false, 'wysiwyg/hello.png', true, '<img src="{{media url=&quot;wysiwyg/hello.png&quot;}}" alt="" />'],
        ];
    }

    /**
     * Generate instance of Images Helper
     *
     * @param bool|null $staticUrlsAllowed - if boolean, mock is created to override value of isUsingStaticUrlsAllowed
     * @return \Magento\Cms\Helper\Wysiwyg\Images
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function generateHelper($staticUrlsAllowed = null)
    {
        if (!is_bool($staticUrlsAllowed)) {
            return $this->objectManager->create(\Magento\Cms\Helper\Wysiwyg\Images::class);
        }

        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $contextMock = $this->objectManager->create(\Magento\Framework\App\Helper\Context::class, [
            'eventManager' => $eventManagerMock,
        ]);

        $helper = $this->objectManager->create(\Magento\Cms\Helper\Wysiwyg\Images::class, [
            'context' => $contextMock
        ]);

        $storeId = 1;
        $helper->setStoreId($storeId);

        $checkResult = new \stdClass();
        $checkResult->isAllowed = false;

        $eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->with('cms_wysiwyg_images_static_urls_allowed', ['result' => $checkResult, 'store_id' => $storeId])
            ->willReturnCallback(function ($_, $arr) use ($staticUrlsAllowed) {
                $arr['result']->isAllowed = $staticUrlsAllowed;
            });

        return $helper;
    }
}
