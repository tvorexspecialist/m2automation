<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * @api
 * @since 2.0.0
 */
interface ScopeInterface
{
    /**
     * Default scope reference code
     */
    const SCOPE_DEFAULT = 'default';

    /**
     * Retrieve scope code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Get scope identifier
     *
     * @return int
     * @since 2.0.0
     */
    public function getId();

    /**
     * Get scope type
     *
     * @return string
     * @since 2.1.0
     */
    public function getScopeType();

    /**
     * Get scope type name
     *
     * @return string
     * @since 2.1.0
     */
    public function getScopeTypeName();

    /**
     * Get scope name
     *
     * @return string
     * @since 2.1.0
     */
    public function getName();
}
