<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Pricing\Price;

use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Downloadable\Model\Link;

/**
 * Class LinkPrice Model
 *
 * @since 2.0.0
 */
class LinkPrice extends RegularPrice implements LinkPriceInterface
{
    /**
     * Default price type
     */
    const PRICE_CODE = 'link_price';

    /**
     * @param Link $link
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     * @since 2.0.0
     */
    public function getLinkAmount(Link $link)
    {
        $price = $link->getPrice();
        $convertedPrice = $this->priceCurrency->convertAndRound($price);
        return $this->calculator->getAmount($convertedPrice, $link->getProduct());
    }
}
