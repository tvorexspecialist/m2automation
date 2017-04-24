<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'arguments' => [
        'data' => [
            'name' => 'data',
            'xsi:type' => 'array',
            'item' => [
                'config' => [
                    'name' => 'config',
                    'xsi:type' => 'array',
                    'item' => [
                        'stickyTmpl' => [
                            'name' => 'stickyTmpl',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'columnsProvider' => [
                            'name' => 'columnsProvider',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'chipsConfig' => [
                            'name' => 'chipsConfig',
                            'xsi:type' => 'array',
                            'item' => [
                                'name' => [
                                    'name' => 'name',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'provider' => [
                                    'name' => 'provider',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'anySimpleType' => [
                                    'name' => 'anySimpleType',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                    'active' => 'false',
                                ],
                            ],
                        ],
                        'templates' => [
                            'name' => 'templates',
                            'xsi:type' => 'array',
                            'item' => [
                                'filters' => [
                                    'name' => 'filters',
                                    'xsi:type' => 'array',
                                    'item' => [
                                        'base' => [
                                            'name' => 'base',
                                            'xsi:type' => 'array',
                                            'item' => [
                                                'anySimpleType' => [
                                                    'name' => 'anySimpleType',
                                                    'xsi:type' => 'string',
                                                    'value' => 'string',
                                                    'active' => 'false',
                                                ],
                                            ],
                                        ],
                                        'text' => [
                                            'name' => 'text',
                                            'xsi:type' => 'array',
                                            'item' => [
                                                'anySimpleType' => [
                                                    'name' => 'anySimpleType',
                                                    'xsi:type' => 'string',
                                                    'value' => 'string',
                                                    'active' => 'false',
                                                ],
                                            ],
                                        ],
                                        'textRange' => [
                                            'name' => 'textRange',
                                            'xsi:type' => 'array',
                                            'item' => [
                                                'anySimpleType' => [
                                                    'name' => 'anySimpleType',
                                                    'xsi:type' => 'string',
                                                    'value' => 'string',
                                                    'active' => 'false',
                                                ],
                                            ],
                                        ],
                                        'dateRange' => [
                                            'name' => 'dateRange',
                                            'xsi:type' => 'array',
                                            'item' => [
                                                'anySimpleType' => [
                                                    'name' => 'anySimpleType',
                                                    'xsi:type' => 'string',
                                                    'value' => 'string',
                                                    'active' => 'false',
                                                ],
                                            ],
                                        ],
                                        'select' => [
                                            'name' => 'select',
                                            'xsi:type' => 'array',
                                            'item' => [
                                                'anySimpleType' => [
                                                    'name' => 'anySimpleType',
                                                    'xsi:type' => 'string',
                                                    'value' => 'string',
                                                    'active' => 'false',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'childDefaults' => [
                            'name' => 'childDefaults',
                            'xsi:type' => 'array',
                            'item' => [
                                'anySimpleType' => [
                                    'name' => 'anySimpleType',
                                    'xsi:type' => 'boolean',
                                    'value' => 'false',
                                    'active' => 'false',
                                ],
                            ],
                        ],
                        'provider' => [
                            'name' => 'provider',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'component' => [
                            'name' => 'component',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'template' => [
                            'name' => 'template',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'sortOrder' => [
                            'name' => 'sortOrder',
                            'xsi:type' => 'number',
                            'value' => '0',
                        ],
                        'displayArea' => [
                            'name' => 'displayArea',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'storageConfig' => [
                            'name' => 'storageConfig',
                            'xsi:type' => 'array',
                            'item' => [
                                'provider' => [
                                    'name' => 'provider',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'namespace' => [
                                    'name' => 'namespace',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                                'path' => [
                                    'name' => 'path',
                                    'xsi:type' => 'url',
                                    'param' => [
                                        'string' => [
                                            'name' => 'string',
                                            'value' => 'string',
                                        ],
                                    ],
                                    'path' => 'string',
                                ],
                            ],
                        ],
                        'statefull' => [
                            'name' => 'statefull',
                            'xsi:type' => 'array',
                            'item' => [
                                'anySimpleType' => [
                                    'name' => 'anySimpleType',
                                    'xsi:type' => 'boolean',
                                    'value' => 'true',
                                ],
                            ],
                        ],
                        'imports' => [
                            'name' => 'imports',
                            'xsi:type' => 'array',
                            'item' => [
                                'string' => [
                                    'name' => 'string',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                        'exports' => [
                            'name' => 'exports',
                            'xsi:type' => 'array',
                            'item' => [
                                'string' => [
                                    'name' => 'string',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                        'links' => [
                            'name' => 'links',
                            'xsi:type' => 'array',
                            'item' => [
                                'string' => [
                                    'name' => 'string',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                        'listens' => [
                            'name' => 'listens',
                            'xsi:type' => 'array',
                            'item' => [
                                'string' => [
                                    'name' => 'string',
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                        'ns' => [
                            'name' => 'ns',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'componentType' => [
                            'name' => 'componentType',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                        'dataScope' => [
                            'name' => 'dataScope',
                            'xsi:type' => 'string',
                            'value' => 'string',
                        ],
                    ],
                ],
                'js_config' => [
                    'name' => 'js_config',
                    'xsi:type' => 'array',
                    'item' => [
                        'deps' => [
                            'name' => 'deps',
                            'xsi:type' => 'array',
                            'item' => [
                                0 => [
                                    'name' => 0,
                                    'xsi:type' => 'string',
                                    'value' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'children' => [],
    'uiComponentType' => 'filters',
];
