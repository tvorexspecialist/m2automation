<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'setup_tests_table1' => [
        'column_with_type_boolean' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_boolean',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'tinyint',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_type_smallint' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_smallint',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'smallint',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_type_integer' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_integer',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'int',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => true,
        ],
        'column_with_type_bigint' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_bigint',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'bigint',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 2,
            'IDENTITY' => false,
        ],
        'column_with_type_float' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_float',
            'COLUMN_POSITION' => 5,
            'DATA_TYPE' => 'float',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_type_numeric' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_numeric',
            'COLUMN_POSITION' => 6,
            'DATA_TYPE' => 'decimal',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => '4',
            'PRECISION' => '12',
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_type_decimal' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_decimal',
            'COLUMN_POSITION' => 7,
            'DATA_TYPE' => 'decimal',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => '4',
            'PRECISION' => '12',
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_type_datetime' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_datetime',
            'COLUMN_POSITION' => 8,
            'DATA_TYPE' => 'datetime',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_type_timestamp_update' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_timestamp_update',
            'COLUMN_POSITION' => 9,
            'DATA_TYPE' => 'timestamp',
            'DEFAULT' => '0000-00-00 00:00:00',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_type_date' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_date',
            'COLUMN_POSITION' => 10,
            'DATA_TYPE' => 'date',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_type_text' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_text',
            'COLUMN_POSITION' => 11,
            'DATA_TYPE' => 'text',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_type_blob' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_blob',
            'COLUMN_POSITION' => 12,
            'DATA_TYPE' => 'varbinary(32)',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_type_verbinary' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_verbinary',
            'COLUMN_POSITION' => 13,
            'DATA_TYPE' => 'mediumblob',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
    ],
    'setup_tests_table1_related' => [
        'column_with_type_timestamp_init_update' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_timestamp_init_update',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'timestamp',
            'DEFAULT' => 'CURRENT_TIMESTAMP',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_type_timestamp_init' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_type_timestamp_init',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'timestamp',
            'DEFAULT' => 'CURRENT_TIMESTAMP',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'column_with_relation' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'column_with_relation',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'int',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
    ],
    'setup_tests_entity_table' => [
        'entity_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'entity_id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'int',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => true,
        ],
        'website_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'website_id',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'smallint',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'email_field' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'email_field',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'increment_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'increment_id',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '50',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'created_at' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'created_at',
            'COLUMN_POSITION' => 5,
            'DATA_TYPE' => 'timestamp',
            'DEFAULT' => 'CURRENT_TIMESTAMP',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'updated_at' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'updated_at',
            'COLUMN_POSITION' => 6,
            'DATA_TYPE' => 'timestamp',
            'DEFAULT' => 'CURRENT_TIMESTAMP',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'created_in' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'created_in',
            'COLUMN_POSITION' => 7,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'firstname' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'firstname',
            'COLUMN_POSITION' => 8,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'lastname' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'lastname',
            'COLUMN_POSITION' => 9,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'dob' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'dob',
            'COLUMN_POSITION' => 10,
            'DATA_TYPE' => 'date',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'default_billing_address_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'default_billing_address_id',
            'COLUMN_POSITION' => 11,
            'DATA_TYPE' => 'int',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'default_shipping_address_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'default_shipping_address_id',
            'COLUMN_POSITION' => 12,
            'DATA_TYPE' => 'int',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
    ],
    'setup_tests_address_entity' => [
        'entity_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'entity_id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'int',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => true,
        ],
        'increment_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'increment_id',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '50',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'parent_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'parent_id',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'int',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'created_at' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'created_at',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'timestamp',
            'DEFAULT' => 'CURRENT_TIMESTAMP',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'updated_at' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'updated_at',
            'COLUMN_POSITION' => 5,
            'DATA_TYPE' => 'timestamp',
            'DEFAULT' => 'CURRENT_TIMESTAMP',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'is_active' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'is_active',
            'COLUMN_POSITION' => 6,
            'DATA_TYPE' => 'smallint',
            'DEFAULT' => '1',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'city' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'city',
            'COLUMN_POSITION' => 7,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'company' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'company',
            'COLUMN_POSITION' => 8,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'country_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'country_id',
            'COLUMN_POSITION' => 9,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'fax' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'fax',
            'COLUMN_POSITION' => 10,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'firstname' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'firstname',
            'COLUMN_POSITION' => 11,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'lastname' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'lastname',
            'COLUMN_POSITION' => 12,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'middlename' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'middlename',
            'COLUMN_POSITION' => 13,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'postcode' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'postcode',
            'COLUMN_POSITION' => 14,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'prefix' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'prefix',
            'COLUMN_POSITION' => 15,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '40',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'region' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'region',
            'COLUMN_POSITION' => 16,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'region_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'region_id',
            'COLUMN_POSITION' => 17,
            'DATA_TYPE' => 'int',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'street' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'street',
            'COLUMN_POSITION' => 18,
            'DATA_TYPE' => 'text',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'suffix' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'suffix',
            'COLUMN_POSITION' => 19,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '40',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'telephone' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'telephone',
            'COLUMN_POSITION' => 20,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => '255',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],

    ],
    'setup_tests_address_entity_datetime' => [
        'value_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'value_id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'int',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => true,
        ],
        'attribute_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'attribute_id',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'smallint',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'entity_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'entity_id',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'int',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'value' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'value',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'datetime',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
    ],
    'setup_tests_address_entity_decimal' => [
        'value_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'value_id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'int',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => true,
        ],
        'attribute_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'attribute_id',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'smallint',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'entity_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'entity_id',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'int',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'value' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'value',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'decimal',
            'DEFAULT' => '0.0000',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => '4',
            'PRECISION' => '12',
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],

    ],
    'setup_tests_entity_passwords' => [
        'password_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'password_id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'int',
            'DEFAULT' => null,
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => true,
        ],
        'entity_id' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'entity_id',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'int',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => null,
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => true,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
        'password_hash' => [
            'SCHEMA_NAME' => null,
            'COLUMN_NAME' => 'password_hash',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => null,
            'NULLABLE' => true,
            'LENGTH' => '100',
            'SCALE' => null,
            'PRECISION' => null,
            'UNSIGNED' => null,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => null,
            'IDENTITY' => false,
        ],
    ]
];
