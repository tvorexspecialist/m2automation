<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

/**
 * @api
 * @since 2.0.0
 */
class AlreadyExistsException extends LocalizedException
{
    /**
     * @param Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @since 2.2.0
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('Unique constraint violation found');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
