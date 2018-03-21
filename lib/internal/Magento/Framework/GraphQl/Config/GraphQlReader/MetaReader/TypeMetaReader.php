<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\GraphQlReader\MetaReader;

class TypeMetaReader
{
    /**
     * Read meta from type meta and parameter type
     *
     * @param $meta
     * @param string $parameterType Argument|OutputField|InputField
     * @return array
     */
    public function readTypeMeta($meta, $parameterType = 'Argument') : array
    {
        $result = [];
        if ($meta instanceof \GraphQL\Type\Definition\NonNull) {
            $result['required'] = true;
            $meta = $meta->getWrappedType();
        } else {
            $result['required'] = false;
        }
        if ($meta instanceof \GraphQL\Type\Definition\ListOfType) {
            $itemTypeMeta = $meta->ofType;
            if ($itemTypeMeta instanceof \GraphQL\Type\Definition\NonNull) {
                $result['itemsRequired'] = true;
                $itemTypeMeta = $itemTypeMeta->getWrappedType();
            } else {
                $result['itemsRequired'] = false;
            }
            $itemTypeName = $itemTypeMeta->name;
            $result['itemType'] = $itemTypeName;
            if ($itemTypeMeta instanceof \GraphQL\Type\Definition\ScalarType) {
                $result['type'] = 'ScalarArray' . $parameterType;
            } else {
                $result['type'] = 'ObjectArray' . $parameterType;
            }
        } else {
            $result['type'] = $meta->name;
        }

        return $result;
    }
}
