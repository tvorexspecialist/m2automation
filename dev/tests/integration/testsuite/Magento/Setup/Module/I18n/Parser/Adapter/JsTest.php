<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter;

/**
 * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Js
 *
 */
class JsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Js
     */
    protected $jsPhraseCollector;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->jsPhraseCollector = $objectManager->create(
            'Magento\Setup\Module\I18n\Parser\Adapter\Js'
        );
    }

    public function testParse()
    {
        $file = __DIR__ . '/_files/jsPhrasesForTest.js';
        $this->jsPhraseCollector->parse($file);
        $expectation = [
            [
                'phrase' => 'text double quote',
                'file' => $file,
                'line' => 1,
                'quote' => '"'
            ],
            [
                'phrase' => 'text single quote',
                'file' => $file,
                'line' => 2,
                'quote' => '\''
            ],
            [
                'phrase' => 'text "some',
                'file' => $file,
                'line' => 3,
                'quote' => '\''
            ]
        ];
        $this->assertEquals($expectation, $this->jsPhraseCollector->getPhrases());
    }
}
