<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\QuoteSession;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Interface QuoteSessionInterface
 */
interface QuoteSessionInterface
{
    /**
     * Returns quote from session.
     *
     * @return CartInterface
     */
    public function getQuote();
}
