<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$entityType = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)
    ->loadByCode('order');
$data = $entityType->getData();
$data['entity_type_code'] = 'test';
unset($data['entity_type_id']);
$testEntityType = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)
    ->setData($data)
    ->save();
$entityTypeId = $testEntityType->getId();

$attributeData = [
    [
        'attribute_code' => 'attribute_for_search_1',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 1,
        'is_user_defined' => 1,
        'is_unique' => 0,
    ],
    [
        'attribute_code' => 'attribute_for_search_2',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 1,
        'is_user_defined' => 1,
        'is_unique' => 0,
    ],
    [
        'attribute_code' => 'attribute_for_search_3',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 1,
        'is_user_defined' => 1,
        'is_unique' => 0,
    ],
    [
        'attribute_code' => 'attribute_for_search_4',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'int',
        'is_required' => 0,
        'is_user_defined' => 1,
        'is_unique' => 0,
    ],
    [
        'attribute_code' => 'attribute_for_search_5',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 0,
        'is_user_defined' => 1,
        'is_unique' => 0,
    ],
];

foreach ($attributeData as $data) {
    /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
    $attribute = $objectManager->create(\Magento\Eav\Model\Entity\Attribute::class);
    $attribute->setData($data);
    $attribute->save();
}
