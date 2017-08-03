<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

/**
 * @deprecated 2.2.0 @see \Magento\Framework\Serialize\Serializer\Json::unserialize
 * @since 2.0.0
 */
class Decoder implements DecoderInterface
{
    /**
     * Decodes the given $data string which is encoded in the JSON format.
     *
     * @param string $data
     * @return mixed
     * @since 2.0.0
     */
    public function decode($data)
    {
        return \Zend_Json::decode($data);
    }
}
