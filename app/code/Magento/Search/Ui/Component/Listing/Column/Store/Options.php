<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Ui\Component\Listing\Column\Store;

use Magento\Store\Ui\Component\Listing\Column\Store\Options as StoreOptions;

/**
 * Class \Magento\Search\Ui\Component\Listing\Column\Store\Options
 *
 * @since 2.1.0
 */
class Options extends StoreOptions
{
    /**
     * Get options
     *
     * @return array
     * @since 2.1.0
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->currentOptions['']['label'] = '--';
        $this->currentOptions['']['value'] = '--';

        $this->currentOptions['All Store Views']['label'] = __('All Store Views');
        $this->currentOptions['All Store Views']['value'] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        $this->generateCurrentOptions();

        $this->options = array_values($this->currentOptions);

        return $this->options;
    }
}
