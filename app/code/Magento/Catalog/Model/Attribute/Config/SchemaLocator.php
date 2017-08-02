<?php
/**
 * Attributes config schema locator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute\Config;

use Magento\Framework\Module\Dir;

/**
 * Class \Magento\Catalog\Model\Attribute\Config\SchemaLocator
 *
 * @since 2.0.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for both individual and merged configs
     *
     * @var string
     * @since 2.0.0
     */
    private $_schema;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Catalog')
            . '/catalog_attributes.xsd';
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPerFileSchema()
    {
        return $this->_schema;
    }
}
