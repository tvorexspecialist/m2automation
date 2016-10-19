<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ProductTypes;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->readerMock = $this->getMock(
            \Magento\Catalog\Model\ProductTypes\Config\Reader::class,
            [],
            [],
            '',
            false
        );
        $this->cacheMock = $this->getMock(\Magento\Framework\Config\CacheInterface::class);
        $this->serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->objectManager->mockObjectManager(
            [\Magento\Framework\Serialize\SerializerInterface::class => $this->serializerMock]
        );
    }

    protected function tearDown()
    {
        $this->objectManager->restoreObjectManager();
    }

    /**
     * @param array $value
     * @param mixed $expected
     * @dataProvider getTypeDataProvider
     */
    public function testGetType($value, $expected)
    {
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->willReturn(json_encode($value));

        $this->serializerMock->method('unserialize')
            ->willReturn($value);
        $this->config = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');
        $this->assertEquals($expected, $this->config->getType('global'));
    }

    public function getTypeDataProvider()
    {
        return [
            'global_key_exist' => [['types' => ['global' => 'value']], 'value'],
            'return_default_value' => [['types' => ['some_key' => 'value']], []]
        ];
    }

    public function testGetAll()
    {
        $expected = ['Expected Data'];
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(json_encode('"types":["Expected Data"]]'));
        $this->serializerMock->method('unserialize')
            ->willReturn(['types' => $expected]);
        $this->config = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');
        $this->assertEquals($expected, $this->config->getAll());
    }

    public function testIsProductSet()
    {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn('');
        $this->serializerMock->method('unserialize')
            ->willReturn([]);
        $this->config = new \Magento\Catalog\Model\ProductTypes\Config($this->readerMock, $this->cacheMock, 'cache_id');

        $this->assertEquals(false, $this->config->isProductSet('typeId'));
    }
}
