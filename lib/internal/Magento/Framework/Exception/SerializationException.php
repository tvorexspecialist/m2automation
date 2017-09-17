<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

/**
 * Serialization Exception
 *
 * @api
 */
class SerializationException extends LocalizedException
{
    /**
     * @deprecated
     */
    const DEFAULT_MESSAGE = 'Invalid type';

    /**
     * @deprecated
     */
    const TYPE_MISMATCH = 'Invalid type for value: "%value". Expected Type: "%type".';

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('One or more input exceptions have occurred.');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
