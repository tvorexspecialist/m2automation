<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'table' => [
        'reference_table' => [
            'column' => [
                'tinyint_ref' => [
                    'type' => 'tinyinteger',
                    'name' => 'tinyint_ref',
                    'default' => 0,
                    'padding' => '7',
                    'nullable' => true,
                    'unsigned' => false,
                    'disabled' => false,
                ],
            ],
            'name' => 'reference_table',
            'resource' => 'sales',
        ],
        'test_table' => [
            'column' => [
                'tinyint' => [
                    'type' => 'tinyinteger',
                    'name' => 'tinyint',
                    'default' => 0,
                    'padding' => '7',
                    'nullable' => true,
                    'unsigned' => false,
                    'disabled' => false,
                ],
            ],
            'constraint' => [
                'some_foreign_key' => [
                    'type' => 'foreign',
                    'name' => 'some_foreign_key',
                    'column' => 'tinyint',
                    'table' => 'test_table',
                    'referenceTable' => 'reference_table',
                    'referenceColumn' => 'tinyint_ref',
                    'nullable' => true,
                    'unsigned' => false,
                    'disabled' => false,
                    'onDelete' => 'CASCADE',
                ],
            ],
            'name' => 'test_table',
            'resource' => 'sales',
        ],
    ],
];
