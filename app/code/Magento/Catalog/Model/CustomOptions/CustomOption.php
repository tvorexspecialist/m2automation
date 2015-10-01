<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\CustomOptions;
//@codeCoverageIgnoreStart
class CustomOption extends \Magento\Framework\Model\AbstractExtensibleModel
    implements \Magento\Catalog\Api\Data\CustomOptionInterface
{
    /**
     * @inheritDoc
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOptionId($value)
    {
        return $this->setData(self::OPTION_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getOptionValue()
    {
        return $this->getData(self::OPTION_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setOptionValue($value)
    {
        return $this->setData(self::OPTION_VALUE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\CustomOptionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
