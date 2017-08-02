<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents converter interface for http request and response body.
 * @since 2.2.0
 */
interface ConverterInterface
{
    /**
     * @param string $body
     *
     * @return array
     * @since 2.2.0
     */
    public function fromBody($body);

    /**
     * @param array $data
     *
     * @return string
     * @since 2.2.0
     */
    public function toBody(array $data);

    /**
     * @return string
     * @since 2.2.0
     */
    public function getContentTypeHeader();
}
