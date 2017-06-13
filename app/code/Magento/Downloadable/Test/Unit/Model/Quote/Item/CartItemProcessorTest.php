<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Quote\Item;

use Magento\Downloadable\Model\Quote\Item\CartItemProcessor;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartItemProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CartItemProcessor
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $optionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $downloadableOptionFactoryMock;

    protected function setUp()
    {
        $this->objectFactoryMock = $this->getMock(
            \Magento\Framework\DataObject\Factory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->optionFactoryMock = $this->getMock(
            \Magento\Quote\Model\Quote\ProductOptionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->objectHelperMock = $this->getMock(\Magento\Framework\Api\DataObjectHelper::class, [], [], '', false);
        $this->extensionFactoryMock = $this->getMock(
            \Magento\Quote\Api\Data\ProductOptionExtensionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->downloadableOptionFactoryMock = $this->getMock(
            \Magento\Downloadable\Model\DownloadableOptionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->model = new CartItemProcessor(
            $this->objectFactoryMock,
            $this->objectHelperMock,
            $this->downloadableOptionFactoryMock,
            $this->optionFactoryMock,
            $this->extensionFactoryMock
        );
    }

    public function testConvertToBuyRequestReturnsNullIfItemDoesNotContainProductOption()
    {
        $cartItemMock = $this->getMock(\Magento\Quote\Api\Data\CartItemInterface::class);
        $this->assertNull($this->model->convertToBuyRequest($cartItemMock));
    }

    public function testConvertToBuyRequest()
    {
        $downloadableLinks = [1, 2];
        $itemQty = 1;

        $cartItemMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProductOption', 'setProductOption', 'getOptionByCode', 'getQty'],
            [],
            '',
            false
        );
        $productOptionMock = $this->getMock(\Magento\Quote\Api\Data\ProductOptionInterface::class);

        $cartItemMock->expects($this->any())->method('getProductOption')->willReturn($productOptionMock);
        $cartItemMock->expects($this->any())->method('getQty')->willReturn($itemQty);
        $extAttributesMock = $this->getMock(
            \Magento\Quote\Api\Data\ProductOption::class,
            ['getDownloadableOption'],
            [],
            '',
            false
        );
        $productOptionMock->expects($this->any())->method('getExtensionAttributes')->willReturn($extAttributesMock);

        $downloadableOptionMock = $this->getMock(\Magento\Downloadable\Api\Data\DownloadableOptionInterface::class);
        $extAttributesMock->expects($this->any())
            ->method('getDownloadableOption')
            ->willReturn($downloadableOptionMock);

        $downloadableOptionMock->expects($this->any())->method('getDownloadableLinks')->willReturn($downloadableLinks);

        $buyRequestData = [
            'links' => $downloadableLinks,
        ];
        $buyRequestMock = new \Magento\Framework\DataObject($buyRequestData);
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with($buyRequestData)
            ->willReturn($buyRequestMock);

        $this->assertEquals($buyRequestMock, $this->model->convertToBuyRequest($cartItemMock));
    }

    public function testProcessProductOptions()
    {
        $downloadableLinks = [1, 2];

        $customOption = $this->getMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option::class,
            [],
            [],
            '',
            false
        );
        $customOption->expects($this->once())->method('getValue')->willReturn(implode(',', $downloadableLinks));

        $cartItemMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProduct', 'getProductOption', 'setProductOption', 'getOptionByCode'],
            [],
            '',
            false
        );
        $cartItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('downloadable_link_ids')
            ->willReturn($customOption);

        $cartItemMock->expects($this->any())
            ->method('getProductOption')
            ->willReturn(null);

        $downloadableOptionMock = $this->getMock(\Magento\Downloadable\Api\Data\DownloadableOptionInterface::class);
        $this->downloadableOptionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($downloadableOptionMock);

        $productOptionMock = $this->getMock(\Magento\Quote\Api\Data\ProductOptionInterface::class);
        $this->optionFactoryMock->expects($this->once())->method('create')->willReturn($productOptionMock);
        $productOptionMock->expects($this->once())->method('getExtensionAttributes')->willReturn(null);

        $extAttributeMock = $this->getMock(
            \Magento\Quote\Api\Data\ProductOptionExtension::class,
            ['setDownloadableOption'],
            [],
            '',
            false
        );

        $this->objectHelperMock->expects($this->once())->method('populateWithArray')->with(
            $downloadableOptionMock,
            [
                'downloadable_links' => $downloadableLinks
            ],
            \Magento\Downloadable\Api\Data\DownloadableOptionInterface::class
        );

        $this->extensionFactoryMock->expects($this->once())->method('create')->willReturn($extAttributeMock);
        $extAttributeMock->expects($this->once())
            ->method('setDownloadableOption')
            ->with($downloadableOptionMock);
        $productOptionMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($extAttributeMock);
        $cartItemMock->expects($this->once())->method('setProductOption')->with($productOptionMock);

        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }

    public function testProcessProductOptionsWhenItemDoesNotHaveDownloadableLinks()
    {
        $downloadableLinks = [];

        $cartItemMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProduct', 'getProductOption', 'setProductOption', 'getOptionByCode'],
            [],
            '',
            false
        );
        $cartItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('downloadable_link_ids');

        $extAttributeMock = $this->getMock(
            \Magento\Quote\Api\Data\ProductOptionExtension::class,
            ['setDownloadableOption'],
            [],
            '',
            false
        );
        $productOptionMock = $this->getMock(\Magento\Quote\Api\Data\ProductOptionInterface::class);
        $productOptionMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extAttributeMock);
        $cartItemMock->expects($this->any())
            ->method('getProductOption')
            ->willReturn($productOptionMock);

        $downloadableOptionMock = $this->getMock(\Magento\Downloadable\Api\Data\DownloadableOptionInterface::class);
        $this->downloadableOptionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($downloadableOptionMock);

        $this->optionFactoryMock->expects($this->never())->method('create');
        $this->extensionFactoryMock->expects($this->never())->method('create');

        $this->objectHelperMock->expects($this->once())->method('populateWithArray')->with(
            $downloadableOptionMock,
            [
                'downloadable_links' => $downloadableLinks
            ],
            \Magento\Downloadable\Api\Data\DownloadableOptionInterface::class
        );

        $extAttributeMock->expects($this->once())
            ->method('setDownloadableOption')
            ->with($downloadableOptionMock);
        $productOptionMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($extAttributeMock);
        $cartItemMock->expects($this->once())->method('setProductOption')->with($productOptionMock);

        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }
}
