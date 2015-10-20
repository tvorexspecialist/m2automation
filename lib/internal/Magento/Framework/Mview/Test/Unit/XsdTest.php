<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Test\Unit;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Path to xsd schema file
     * @var string
     */
    protected $_xsdSchema;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Utility\XsdValidator
     */
    protected $_xsdValidator;

    protected function setUp()
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->_xsdSchema = $urnResolver->getRealPath('urn:magento:framework:Mview/etc/mview.xsd');
        $this->_xsdValidator = new \Magento\Framework\TestFramework\Unit\Utility\XsdValidator();
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesInvalidXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidXml($xmlString, $expectedError)
    {
        $actualError = $this->_xsdValidator->validate($this->_xsdSchema, $xmlString);
        $this->assertEquals($expectedError, $actualError);
    }

    public function testSchemaCorrectlyIdentifiesValidXml()
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/valid_mview.xml');
        $actualResult = $this->_xsdValidator->validate($this->_xsdSchema, $xmlString);

        $this->assertEmpty($actualResult);
    }

    /**
     * Data provider with invalid xml array according to events.xsd
     */
    public function schemaCorrectlyIdentifiesInvalidXmlDataProvider()
    {
        return include __DIR__ . '/_files/invalidMviewXmlArray.php';
    }
}
