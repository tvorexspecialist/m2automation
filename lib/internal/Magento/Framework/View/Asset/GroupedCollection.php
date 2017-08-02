<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * List of page assets that combines into groups ones having the same properties
 *
 * @api
 * @since 2.0.0
 */
class GroupedCollection extends Collection
{
    /**#@+
     * Special properties, enforced to be grouped by
     */
    const PROPERTY_CONTENT_TYPE = 'content_type';

    const PROPERTY_CAN_MERGE = 'can_merge';

    /**#@-*/

    /**
     * Property Factory
     *
     * @var \Magento\Framework\View\Asset\PropertyGroupFactory
     * @since 2.0.0
     */
    protected $propertyFactory;

    /**
     * Property Groups
     *
     * @var PropertyGroup[]
     * @since 2.0.0
     */
    protected $groups = [];

    /**
     * Constructor
     *
     * @param PropertyGroupFactory $propertyFactory
     * @since 2.0.0
     */
    public function __construct(PropertyGroupFactory $propertyFactory)
    {
        $this->propertyFactory = $propertyFactory;
    }

    /**
     * Add an instance, identified by a unique identifier, to the list and to the corresponding group
     *
     * @param string $identifier
     * @param AssetInterface $asset
     * @param array $properties
     * @return void
     * @since 2.0.0
     */
    public function add($identifier, AssetInterface $asset, array $properties = [])
    {
        parent::add($identifier, $asset);
        $properties = $this->getFilteredProperties($asset, $properties);
        $this->getGroupFor($properties)->add($identifier, $asset);
    }

    /**
     * @param string $identifier
     * @param AssetInterface $asset
     * @param string $key
     * @return void
     * @since 2.0.0
     */
    public function insert($identifier, AssetInterface $asset, $key)
    {
        parent::insert($identifier, $asset, $key);
        $properties = $this->getFilteredProperties($asset);
        $this->getGroupFor($properties)->insert($identifier, $asset, $key);
    }

    /**
     * @param AssetInterface $asset
     * @param array $properties
     * @return array
     * @since 2.0.0
     */
    public function getFilteredProperties(AssetInterface $asset, $properties = [])
    {
        $properties = array_filter($properties);
        $properties[self::PROPERTY_CONTENT_TYPE] = $asset->getContentType();
        $properties[self::PROPERTY_CAN_MERGE] = $asset instanceof MergeableInterface;

        return $properties;
    }

    /**
     * Retrieve existing or new group matching the properties
     *
     * @param array $properties
     * @return PropertyGroup
     * @since 2.0.0
     */
    private function getGroupFor(array $properties)
    {
        /** @var $existingGroup PropertyGroup */
        foreach ($this->groups as $existingGroup) {
            if ($existingGroup->getProperties() == $properties) {
                return $existingGroup;
            }
        }
        /** @var $newGroup PropertyGroup */
        $newGroup = $this->propertyFactory->create(['properties' => $properties]);
        $this->groups[] = $newGroup;
        return $newGroup;
    }

    /**
     * Remove an instance from the list and from the corresponding group
     *
     * @param string $identifier
     * @return void
     * @since 2.0.0
     */
    public function remove($identifier)
    {
        parent::remove($identifier);
        /** @var PropertyGroup $group  */
        foreach ($this->groups as $group) {
            if ($group->has($identifier)) {
                $group->remove($identifier);
                return;
            }
        }
    }

    /**
     * Retrieve groups, containing assets that have the same properties
     *
     * @return PropertyGroup[]
     * @since 2.0.0
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Get asset group by content type
     *
     * @param string $contentType
     * @return bool|PropertyGroup
     * @since 2.0.0
     */
    public function getGroupByContentType($contentType)
    {
        foreach ($this->groups as $group) {
            if ($group->getProperty(self::PROPERTY_CONTENT_TYPE) == $contentType) {
                return $group;
            }
        }

        return false;
    }
}
