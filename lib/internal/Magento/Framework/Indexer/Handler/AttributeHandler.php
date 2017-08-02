<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Handler;

use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Magento\Framework\Indexer\HandlerInterface;

/**
 * Class \Magento\Framework\Indexer\Handler\AttributeHandler
 *
 * @since 2.0.0
 */
class AttributeHandler implements HandlerInterface
{
    /**
     * Prepare SQL for field and add it to collection
     *
     * @param SourceProviderInterface $source
     * @param string $alias
     * @param array $fieldInfo
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function prepareSql(SourceProviderInterface $source, $alias, $fieldInfo)
    {
        if (isset($fieldInfo['bind'])) {
            if (!method_exists($source, 'joinAttribute')) {
                return;
            }

            $source->joinAttribute(
                $fieldInfo['name'],
                $fieldInfo['entity'] . '/' . $fieldInfo['origin'],
                $fieldInfo['bind'],
                null,
                'left'
            );
        } else {
            $source->addAttributeToSelect($fieldInfo['origin'], 'left');
        }
    }
}
