<?php
/**
 * Test indexer.xsd and xml files.
 *
 * Find "indexer.xml" files in code tree and validate them.  Also verify schema fails on an invalid xml and
 * passes on a valid xml.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Indexer;

class ConfigTest extends \Magento\TestFramework\Integrity\AbstractConfig
{
    /**
     * Returns the name of the XSD file to be used to validate the XML
     *
     * @return string
     */
    protected function _getXsd()
    {
        return '/app/code/Magento/Indexer/etc/indexer.xsd';
    }

    /**
     * The location of a single valid complete xml file
     *
     * @return string
     */
    protected function _getKnownValidXml()
    {
        return __DIR__ . '/_files/valid_indexer.xml';
    }

    /**
     * The location of a single known invalid complete xml file
     *
     * @return string
     */
    protected function _getKnownInvalidXml()
    {
        return __DIR__ . '/_files/invalid_indexer.xml';
    }

    /**
     * The location of a single known valid partial xml file
     *
     * @return string
     */
    protected function _getKnownValidPartialXml()
    {
        return '';
    }

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    protected function _getFileXsd()
    {
        return '/app/code/Magento/Indexer/etc/indexer.xsd';
    }

    /**
     * The location of a single known invalid partial xml file
     *
     * @return string
     */
    protected function _getKnownInvalidPartialXml()
    {
        return '';
    }

    /**
     * Returns the name of the xml files to validate
     *
     * @return string
     */
    protected function _getXmlName()
    {
        return 'indexer.xml';
    }

    public function testFileSchemaUsingInvalidXml($expectedErrors = null)
    {
        $this->markTestSkipped('indexer.xml does not have a partial schema');
    }

    public function testSchemaUsingPartialXml($expectedErrors = null)
    {
        $this->markTestSkipped('indexer.xml does not have a partial schema');
    }

    public function testFileSchemaUsingPartialXml()
    {
        $this->markTestSkipped('indexer.xml does not have a partial schema');
    }

    public function testSchemaUsingInvalidXml($expectedErrors = null)
    {
        $expectedErrors = array_filter(
            explode(
                "\n",
                "
Element 'indexer': Duplicate key-sequence ['catalogsearch_fulltext'] in unique identity-constraint 'uniqueViewId'.
Element 'indexer': Duplicate key-sequence ['indexer_0', 'catalogsearch_fulltext'] in unique identity-constraint" .
    " 'uniqueIndexertId'.
Element 'field', attribute 'handler': [facet 'pattern'] " .
    "The value 'Magento\\Framework\\Search\\Index\\Field\\Handler\\Class' is not accepted by the pattern " .
    "'[a-zA-Z0-9_]+'.
Element 'field', attribute 'handler': 'Magento\\Framework\\Search\\Index\\Field\\Handler\\Class' is not a valid " .
    "value of the atomic type 'nameType'.
Element 'field': Duplicate key-sequence ['visibility'] in unique identity-constraint 'uniqueField'.
Element 'field', attribute 'source': [facet 'pattern'] The value 'Magento\\Framework\\Search\\Index\\Source' " .
    "is not accepted by the pattern '[a-zA-Z0-9_]+'.
Element 'field', attribute 'source': 'Magento\\Framework\\Search\\Index\\Source' is not a valid " .
    "value of the atomic type 'nameType'.
Element 'field': The attribute 'dataType' is required but missing.
Element 'field', attribute '{http://www.w3.org/2001/XMLSchema-instance}type': The QName value 'any'" .
    " of the xsi:type attribute does not resolve to a type definition.
Element 'field', attribute 'dataType': [facet 'enumeration'] The value 'string' is not an element" .
    " of the set {'int', 'float', 'varchar'}.
Element 'field', attribute 'dataType': 'string' is not a valid value of the atomic type 'dataType'."
            )
        );
        parent::testSchemaUsingInvalidXml($expectedErrors);
    }
}
