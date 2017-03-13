<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Setup;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Unit test for serialized data converter test.
 */
class SerializedDataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Setup\SerializedDataConverter
     */
    protected $model;

    /**
     * @var Serialize|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializeMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonMock;

    public function setUp()
    {
        $this->serializeMock = $this->getMock(Serialize::class, ['unserialize'], [], '', false);
        $this->jsonMock = $this->getMock(Json::class, ['serialize'], [], '', false);
        $this->model = new \Magento\Sales\Setup\SerializedDataConverter($this->serializeMock, $this->jsonMock);
    }

    /**
     * Init serializer mock with default serialize and unserialize callbacks
     */
    protected function initSerializerMock()
    {
        $this->serializeMock->expects($this->any())
            ->method('unserialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return unserialize($value);
                    }
                )
            );
        $this->jsonMock->expects($this->any())
            ->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );
    }

    /**
     * @param string $serialized
     * @param string $expectedJson
     *
     * @dataProvider convertDataProvider
     */
    public function testConvert($serialized, $expectedJson)
    {
        $this->initSerializerMock();
        $this->assertEquals($expectedJson, $this->model->convert($serialized));
    }

    /**
     * @param string $serialized
     * @param string $expectedJson
     *
     * @dataProvider convertDataProvider
     *
     * @expectedException \Magento\Framework\DB\DataConverter\DataConversionException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testConvertCorruptedData($serialized, $expectedJson)
    {
        $this->initSerializerMock();
        $serialized = substr($serialized, 0, 50);
        $this->model->convert($serialized);
    }

    /**
     * Test skipping deserialization and json_encoding of valid JSON encoded string
     */
    public function testSkipJsonDataConversion()
    {
        $serialized = '[]';
        $this->serializeMock->expects($this->never())->method('unserialize');
        $this->jsonMock->expects($this->never())->method('serialize');
        $this->model->convert($serialized);
    }

    /**
     * Data provider for convert method test
     *
     * Slashes in PHP string are implicitly escaped so they MUST be escaped manually to correspond real expected data
     *
     * @return array
     */
    public function convertDataProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            'dataset_1' => [
                'serialized' => 'a:2:{s:15:"info_buyRequest";a:6:{s:4:"uenc";s:52:"aHR0cDovL20yLmxvYy9zaW1wbGUuaHRtbD9vcHRpb25zPWNhcnQ,";s:7:"product";s:1:"1";s:28:"selected_configurable_option";s:0:"";s:15:"related_product";s:0:"";s:7:"options";a:3:{i:1;s:4:"test";i:3;s:1:"2";i:2;a:9:{s:4:"type";s:10:"image/jpeg";s:5:"title";s:7:"476.jpg";s:10:"quote_path";s:61:"custom_options/quote/4/7/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg";s:10:"order_path";s:61:"custom_options/order/4/7/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg";s:8:"fullpath";s:89:"C:/www/magento/ce/pub/media/custom_options/quote/4/7/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg";s:4:"size";s:6:"122340";s:5:"width";i:666;s:6:"height";i:940;s:10:"secret_key";s:20:"bc61f16f0cc3a8c5abd7";}}s:3:"qty";s:1:"1";}s:7:"options";a:3:{i:0;a:7:{s:5:"label";s:11:"testoption1";s:5:"value";s:4:"test";s:11:"print_value";s:4:"test";s:9:"option_id";s:1:"1";s:11:"option_type";s:5:"field";s:12:"option_value";s:4:"test";s:11:"custom_view";b:0;}i:1;a:7:{s:5:"label";s:11:"testoption2";s:5:"value";s:132:"<a href="http://m2.loc/sales/download/downloadCustomOption/id/9/key/bc61f16f0cc3a8c5abd7/" target="_blank">476.jpg</a> 666 x 940 px.";s:11:"print_value";s:21:"476.jpg 666 x 940 px.";s:9:"option_id";s:1:"2";s:11:"option_type";s:4:"file";s:12:"option_value";s:600:"a:10:{s:4:"type";s:10:"image/jpeg";s:5:"title";s:7:"476.jpg";s:10:"quote_path";s:61:"custom_options/quote/4/7/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg";s:10:"order_path";s:61:"custom_options/order/4/7/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg";s:8:"fullpath";s:89:"C:/www/magento/ce/pub/media/custom_options/quote/4/7/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg";s:4:"size";s:6:"122340";s:5:"width";i:666;s:6:"height";i:940;s:10:"secret_key";s:20:"bc61f16f0cc3a8c5abd7";s:3:"url";a:2:{s:5:"route";s:35:"sales/download/downloadCustomOption";s:6:"params";a:2:{s:2:"id";s:1:"9";s:3:"key";s:20:"bc61f16f0cc3a8c5abd7";}}}";s:11:"custom_view";b:1;}i:2;a:7:{s:5:"label";s:8:"testopt3";s:5:"value";s:3:"222";s:11:"print_value";s:3:"222";s:9:"option_id";s:1:"3";s:11:"option_type";s:9:"drop_down";s:12:"option_value";s:1:"2";s:11:"custom_view";b:0;}}}',
                'expectedJson' => '{"info_buyRequest":{"uenc":"aHR0cDovL20yLmxvYy9zaW1wbGUuaHRtbD9vcHRpb25zPWNhcnQ,","product":"1","selected_configurable_option":"","related_product":"","options":{"1":"test","3":"2","2":{"type":"image\/jpeg","title":"476.jpg","quote_path":"custom_options\/quote\/4\/7\/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg","order_path":"custom_options\/order\/4\/7\/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg","fullpath":"C:\/www\/magento\/ce\/pub\/media\/custom_options\/quote\/4\/7\/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg","size":"122340","width":666,"height":940,"secret_key":"bc61f16f0cc3a8c5abd7"}},"qty":"1"},"options":[{"label":"testoption1","value":"test","print_value":"test","option_id":"1","option_type":"field","option_value":"test","custom_view":false},{"label":"testoption2","value":"<a href=\"http:\/\/m2.loc\/sales\/download\/downloadCustomOption\/id\/9\/key\/bc61f16f0cc3a8c5abd7\/\" target=\"_blank\">476.jpg<\/a> 666 x 940 px.","print_value":"476.jpg 666 x 940 px.","option_id":"2","option_type":"file","option_value":"{\"type\":\"image\\\\\/jpeg\",\"title\":\"476.jpg\",\"quote_path\":\"custom_options\\\\\/quote\\\\\/4\\\\\/7\\\\\/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg\",\"order_path\":\"custom_options\\\\\/order\\\\\/4\\\\\/7\\\\\/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg\",\"fullpath\":\"C:\\\\\/www\\\\\/magento\\\\\/ce\\\\\/pub\\\\\/media\\\\\/custom_options\\\\\/quote\\\\\/4\\\\\/7\\\\\/bc61f16f0cc3a8c5abd7ebbaad4cc139.jpg\",\"size\":\"122340\",\"width\":666,\"height\":940,\"secret_key\":\"bc61f16f0cc3a8c5abd7\",\"url\":{\"route\":\"sales\\\\\/download\\\\\/downloadCustomOption\",\"params\":{\"id\":\"9\",\"key\":\"bc61f16f0cc3a8c5abd7\"}}}","custom_view":true},{"label":"testopt3","value":"222","print_value":"222","option_id":"3","option_type":"drop_down","option_value":"2","custom_view":false}]}',
            ],
            'dataset_2' => [
                'serialized' => 'a:2:{s:15:"info_buyRequest";a:6:{s:4:"uenc";s:36:"aHR0cDovL20yLmxvYy9idW5kbGUuaHRtbA,,";s:7:"product";s:1:"4";s:28:"selected_configurable_option";s:0:"";s:15:"related_product";s:0:"";s:13:"bundle_option";a:2:{i:1;s:1:"1";i:2;s:1:"2";}s:3:"qty";s:1:"3";}s:27:"bundle_selection_attributes";s:97:"a:4:{s:5:"price";d:100;s:3:"qty";d:1;s:12:"option_label";s:8:"option 1";s:9:"option_id";s:1:"1";}";}',
                'expectedJson' => '{"info_buyRequest":{"uenc":"aHR0cDovL20yLmxvYy9idW5kbGUuaHRtbA,,","product":"4","selected_configurable_option":"","related_product":"","bundle_option":{"1":"1","2":"2"},"qty":"3"},"bundle_selection_attributes":"{\"price\":100,\"qty\":1,\"option_label\":\"option 1\",\"option_id\":\"1\"}"}',
            ],
        ];
        // @codingStandardsIgnoreEnd
    }
}
