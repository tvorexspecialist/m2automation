<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * This interface provides all params, that should participate in elements comparison
 */
interface ElementDiffAwareInterface
{
    /**
     * Return sensitive params, with respect of which we will compare db and xml
     * For instance,
     *  padding => '2'
     *  identity => null
     *
     * Such params as name, renamedTo, disabled, tableName should be avoided here
     * As this params are system and must not participate in comparison at all
     *
     * @return array
     */
    public function getDiffSensitiveParams();
}
