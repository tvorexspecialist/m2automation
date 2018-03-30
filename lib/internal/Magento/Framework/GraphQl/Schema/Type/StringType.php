<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl StringType
 */
class StringType extends \GraphQL\Type\Definition\StringType implements InputType, OutputType
{
    /**
     * @var string
     */
    public $name = "Magento_String";
}
