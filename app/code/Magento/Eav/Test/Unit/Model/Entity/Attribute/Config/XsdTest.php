<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Config;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Path to xsd schema file
     * @var string
     */
    protected $_xsdSchema;

    /**
     * @var \Magento\TestFramework\Utility\XsdValidator
     */
    protected $_xsdValidator;

    protected function setUp()
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->_xsdSchema = $urnResolver->getRealPath('urn:magento:module:Magento_EAV:etc/eav_attributes.xsd');
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
        $xmlString = file_get_contents(__DIR__ . '/_files/eav_attributes.xml');
        $actualResult = $this->_xsdValidator->validate($this->_xsdSchema, $xmlString);
        $this->assertEmpty($actualResult);
    }

    /**
     * Data provider with invalid xml array according to eav_attribute.xsd
     */
    public function schemaCorrectlyIdentifiesInvalidXmlDataProvider()
    {
        return include __DIR__ . '/_files/invalidEavAttributeXmlArray.php';
    }
}
