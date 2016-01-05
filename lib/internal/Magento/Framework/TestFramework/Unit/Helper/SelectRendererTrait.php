<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Helper;

/**
 * Class SelectRendererTrait
 */
trait SelectRendererTrait
{
    /**
     * @param \Magento\Framework\TestFramework\Unit\Helper\ObjectManager $objectManager
     * @return \Magento\Framework\DB\Select\SelectRenderer
     */
    protected function getSelectRenderer(\Magento\Framework\TestFramework\Unit\Helper\ObjectManager $objectManager)
    {
        return $objectManager->getObject(
            'Magento\Framework\DB\Select\SelectRenderer',
            [
                'renderers' => [
                    'distinct' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\DistinctRenderer'
                        ),
                        'sort' => 11,
                    ],
                    'columns' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\ColumnsRenderer',
                            [
                                'quote' => $objectManager->getObject('Magento\Framework\DB\Platform\Quote')
                            ]
                        ),
                        'sort' => 11,
                    ],
                    'union' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\UnionRenderer'
                        ),
                        'sort' => 11,
                    ],
                    'from' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\FromRenderer',
                            [
                                'quote' => $objectManager->getObject('Magento\Framework\DB\Platform\Quote')
                            ]
                        ),
                        'sort' => 11,
                    ],
                    'where' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\WhereRenderer'
                        ),
                        'sort' => 11,
                    ],
                    'group' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\GroupRenderer'
                        ),
                        'sort' => 11,
                    ],
                    'having' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\HavingRenderer'
                        ),
                        'sort' => 11,
                    ],
                    'order' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\OrderRenderer'
                        ),
                        'sort' => 11,
                    ],
                    'limit' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\LimitRenderer'
                        ),
                        'sort' => 11,
                    ],
                ],
            ]
        );
    }
}
