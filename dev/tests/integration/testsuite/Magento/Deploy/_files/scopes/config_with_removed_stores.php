<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return array(
    'scopes' => array(
        'stores' =>
            array(
                'admin' =>
                    array(
                        'store_id' => '0',
                        'code' => 'admin',
                        'website_id' => '0',
                        'group_id' => '0',
                        'name' => 'Admin24',
                        'sort_order' => '10',
                        'is_active' => '1',
                    ),
                'default' =>
                    array(
                        'store_id' => '1',
                        'code' => 'default',
                        'website_id' => '1',
                        'group_id' => '1',
                        'name' => 'Default Store View',
                        'sort_order' => '0',
                        'is_active' => '1',
                    ),
            ),
        'websites' =>
            array(
                'admin' =>
                    array(
                        'website_id' => '0',
                        'code' => 'admin',
                        'name' => 'Admin',
                        'sort_order' => '0',
                        'default_group_id' => '0',
                        'is_default' => '0',
                    ),
                'base' =>
                    array(
                        'website_id' => '1',
                        'code' => 'base',
                        'name' => 'Main Website',
                        'sort_order' => '0',
                        'default_group_id' => '1',
                        'is_default' => '1',
                    ),
            ),
        'groups' =>
            array(
                0 =>
                    array(
                        'group_id' => '0',
                        'website_id' => '0',
                        'name' => 'Default',
                        'root_category_id' => '0',
                        'default_store_id' => '0',
                        'code' => 'default',
                    ),
                1 =>
                    array(
                        'group_id' => '1',
                        'website_id' => '1',
                        'name' => 'Main Website Store',
                        'root_category_id' => '2',
                        'default_store_id' => '1',
                        'code' => 'main_website_store',
                    ),
            ),
    )
);
