<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Adapter\Mysql\Query\Preprocessor;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SynonymsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Api\SynonymAnalyzerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $synonymAnalyzer;

    /**
     * @var \Magento\Search\Adapter\Mysql\Query\Preprocessor\Synonyms
     */
    private $synonymPreprocessor;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->synonymAnalyzer = $this->getMockBuilder('Magento\Search\Model\SynonymAnalyzer')
            ->setMethods(['getSynonymsForPhrase'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->synonymPreprocessor = $objectManager->getObject(
            'Magento\Search\Adapter\Mysql\Query\Preprocessor\Synonyms',
            [
                'synonymsAnalyzer' => $this->synonymAnalyzer
            ]
        );
    }

    /**
     * Data provider for the test
     *
     * @return array
     */
    public static function loadProcessDataProvider()
    {
        return [
            'oneWord' => [
                'query' => 'big',
                'result' => [['big', 'huge']],
                'newQuery' => 'big huge'
            ],
            'twoWords' => [
                'query' => 'big universe',
                'result' => [['big', 'huge'], ['universe', 'cosmos']],
                'newQuery' => 'big huge universe cosmos'
            ],
            'noSynonyms' => [
                'query' => 'no synonyms',
                'result' => [['no'], ['synonyms']],
                'newQuery' => 'no synonyms'
            ]
        ];
    }

    /**
     * @param string $phrase
     * @param array $expectedResult
     * @dataProvider loadProcessDataProvider
     */
    public function testProcess($query, $result, $newQuery)
    {
        $this->synonymAnalyzer->expects($this->once())
            ->method('getSynonymsForPhrase')
            ->with($this->equalTo($query))
            ->will($this->returnValue($result));

        $result = $this->synonymPreprocessor->process($query);
        $this->assertEquals($result, $newQuery);
    }
}
