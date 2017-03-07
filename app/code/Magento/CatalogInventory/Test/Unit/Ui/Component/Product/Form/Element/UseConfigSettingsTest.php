<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Ui\Component\Product\Form\Element;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CatalogInventory\Ui\Component\Product\Form\Element\UseConfigSettings;
use Magento\Framework\Data\ValueSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class UseConfigSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $processorMock = $this->getMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class,
            [],
            [],
            '',
            false,
            false
        );
        $processorMock->expects($this->once())
            ->method('register');
        $this->contextMock = $this->getMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $this->contextMock->expects($this->any())
            ->method('getProcessor')
            ->willReturn($processorMock);
        $this->serializerMock = $this->getMock(Json::class);
    }

    /**
     * @return void
     */
    public function testPrepare()
    {
        $config = ['valueFromConfig' => 123];
        $element = $this->getTestedElement($config);
        $element->prepare();
        $this->assertEquals($config, $element->getData('config'));
    }

    /**
     * @return void
     * @dataProvider prepareSourceDataProvider
     */
    public function testPrepareSource($expectedResult, $sourceValue, $serializedCallCount = 0)
    {
        /** @var ValueSourceInterface|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->getMock(ValueSourceInterface::class);
        $source->expects($this->once())
            ->method('getValue')
            ->with($expectedResult['keyInConfiguration'])
            ->willReturn($sourceValue);

        $this->serializerMock->expects($this->exactly($serializedCallCount))
            ->method('unserialize')
            ->with($sourceValue)
            ->willReturn($expectedResult['valueFromConfig']);

        $config = array_replace($expectedResult, ['valueFromConfig' => $source]);
        $element = $this->getTestedElement($config);
        $element->prepare();

        $this->assertEquals($expectedResult, $element->getData('config'));
    }

    public function prepareSourceDataProvider()
    {
        return [
            'valid' => [
                'expectedResult' => [
                    'valueFromConfig' => 2,
                    'keyInConfiguration' => 'validKey'
                ],
                'sourceValue' => 2
            ],
            'serialized' => [
                'expectedResult' => [
                    'valueFromConfig' => ['32000' => 3],
                    'keyInConfiguration' => 'serializedKey',
                    'unserialized' => true
                ],
                'sourceValue' => '{"32000":3}',
                'serialziedCallCount' => 1
            ]
        ];
    }

    /**
     * @param array $config
     * @return UseConfigSettings|object
     */
    private function getTestedElement(array $config = [])
    {
        return $this->objectManagerHelper->getObject(
            UseConfigSettings::class,
            [
                'context' => $this->contextMock,
                'data' => ['config' => $config],
                'serializer' => $this->serializerMock
            ]
        );
    }
}
