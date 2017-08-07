<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\StrategyInterface;

/**
 * Produce cache tags using IdentityInterface
 * @since 2.1.3
 */
class Identifier implements StrategyInterface
{
    /**
     * {@inheritdoc}
     * @since 2.1.3
     */
    public function getTags($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }

        if ($object instanceof \Magento\Framework\DataObject\IdentityInterface) {
            return $object->getIdentities();
        }

        return [];
    }
}
