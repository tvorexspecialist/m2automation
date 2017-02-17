<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Api\Data;

/**
 * Interface LinkInterface
 *
 * Represents link with collected data and initialized vector for decryption.
 */
interface LinkInterface
{
    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return string
     */
    public function getInitializedVector();

    /**
     * @param string $url
     * @return void
     */
    public function setUrl($url);

    /**
     * @param string $initializedVector
     * @return void
     */
    public function setInitializedVector($initializedVector);
}
