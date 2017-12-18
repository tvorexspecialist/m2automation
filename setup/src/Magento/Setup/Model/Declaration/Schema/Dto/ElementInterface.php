<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * This is generic interface
 *
 * It is parent interface for all various schema structural elements:
 * table, column, constaint, index
 */
interface ElementInterface
{
    /**
     * Return customer name of structural element
     *
     * @return string
     */
    public function getName();

    /**
     * Retrieve structural element type: column, constraint, table, index ...
     *
     * @return string
     */
    public function getElementType();
}
