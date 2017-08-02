<?php
/**
 * Authorization service exception
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

/**
 * @api
 * @since 2.0.0
 */
class AuthorizationException extends LocalizedException
{
    /**
     * @deprecated
     */
    const NOT_AUTHORIZED = 'Consumer is not authorized to access %resources';
}
