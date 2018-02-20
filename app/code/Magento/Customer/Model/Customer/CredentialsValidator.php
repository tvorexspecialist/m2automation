<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Customer;

use Magento\Framework\Exception\InputException;

/**
 * Class to invalidate user credentials
 */
class CredentialsValidator
{
    /**
     * Check that password is different from email.
     *
     * @param string $email
     * @param string $password
     * @return void
     * @throws InputException
     */
    public function checkPasswordDifferentFromEmail($email, $password)
    {
        if (strcasecmp($password, $email) == 0) {
            throw new InputException(
                __("The password can't be the same as the email address. Create a new password and try again.")
            );
        }
    }
}
