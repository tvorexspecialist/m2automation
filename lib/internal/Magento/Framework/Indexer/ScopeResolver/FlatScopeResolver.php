<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\ScopeResolver;

use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Class \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver
 *
 * @since 2.0.0
 */
class FlatScopeResolver implements IndexScopeResolverInterface
{
    const SUFFIX_FLAT = '_flat';

    /**
     * @var IndexScopeResolver
     * @since 2.0.0
     */
    private $indexScopeResolver;

    /**
     * @param IndexScopeResolver $indexScopeResolver
     * @since 2.0.0
     */
    public function __construct(IndexScopeResolver $indexScopeResolver)
    {
        $this->indexScopeResolver = $indexScopeResolver;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function resolve($index, array $dimensions)
    {
        return $this->indexScopeResolver->resolve($index, []) . self::SUFFIX_FLAT;
    }
}
