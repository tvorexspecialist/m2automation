<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Email;

/**
 * @api
 * @since 2.0.0
 */
class Smtpauth implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'NONE', 'label' => 'NONE'],
            ['value' => 'PLAIN', 'label' => 'PLAIN'],
            ['value' => 'LOGIN', 'label' => 'LOGIN'],
            ['value' => 'CRAM-MD5', 'label' => 'CRAM-MD5']
        ];
    }
}
