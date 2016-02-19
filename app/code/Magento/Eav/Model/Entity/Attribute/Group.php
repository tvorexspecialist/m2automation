<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Framework\Api\AttributeValueFactory;

/**
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group _getResource()
 * @method \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group getResource()
 * @method int getSortOrder()
 * @method \Magento\Eav\Model\Entity\Attribute\Group setSortOrder(int $value)
 * @method int getDefaultId()
 * @method \Magento\Eav\Model\Entity\Attribute\Group setDefaultId(int $value)
 * @method string getAttributeGroupCode()
 * @method \Magento\Eav\Model\Entity\Attribute\Group setAttributeGroupCode(string $value)
 * @method string getTabGroupCode()
 * @method \Magento\Eav\Model\Entity\Attribute\Group setTabGroupCode(string $value)
 */
class Group extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Eav\Api\Data\AttributeGroupInterface
{
    /**
     * @var \Magento\Framework\Filter\Translit
     */
    private $translitFilter;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Filter\Translit $translitFilter
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Filter\Translit $translitFilter,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->translitFilter = $translitFilter;
    }

    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\ResourceModel\Entity\Attribute\Group');
    }

    /**
     * Checks if current attribute group exists
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function itemExists()
    {
        return $this->_getResource()->itemExists($this);
    }

    /**
     * Delete groups
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function deleteGroups()
    {
        return $this->_getResource()->deleteGroups($this);
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        if (!$this->getAttributeGroupCode()) {
            $groupName = $this->getAttributeGroupName();
            if ($groupName) {
                $attributeGroupCode = trim(
                    preg_replace(
                        '/[^a-z0-9]+/',
                        '-',
                        $this->translitFilter->filter(strtolower($groupName))
                    ),
                    '-'
                );
                if (empty($attributeGroupCode)) {
                    // in the following code md5 is not used for security purposes
                    $attributeGroupCode = md5($groupName);
                }
                $this->setAttributeGroupCode($attributeGroupCode);
            }
        }
        return parent::beforeSave();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnoreStart
     */
    public function getAttributeGroupId()
    {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeGroupName()
    {
        return $this->getData(self::GROUP_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSetId()
    {
        return $this->getData(self::ATTRIBUTE_SET_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeGroupId($attributeGroupId)
    {
        return $this->setData(self::GROUP_ID, $attributeGroupId);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeGroupName($attributeGroupName)
    {
        return $this->setData(self::GROUP_NAME, $attributeGroupName);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeSetId($attributeSetId)
    {
        return $this->setData(self::ATTRIBUTE_SET_ID, $attributeSetId);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Eav\Api\Data\AttributeGroupExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Eav\Api\Data\AttributeGroupExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Eav\Api\Data\AttributeGroupExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    //@codeCoverageIgnoreEnd
}
