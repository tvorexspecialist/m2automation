<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\GroupedProductGraphQl\Model;

use Magento\Framework\GraphQl\Config\Data\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class GroupedProductLinksTypeResolver implements TypeResolverInterface
{
    /**
     * @var string[]
     */
    private $linkTypes = ['associated'];

    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data) : ?string
    {
        if (isset($data['link_type'])) {
            $linkType = $data['link_type'];
            if (in_array($linkType, $this->linkTypes)) {
                return 'GroupedProductLinks';
            }
        }
        return null;
    }
}
