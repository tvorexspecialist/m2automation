<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

/**
 * Class \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency\SaveRates
 *
 * @since 2.0.0
 */
class SaveRates extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency
{
    /**
     * Save rates action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('rate');
        if (is_array($data)) {
            try {
                foreach ($data as $currencyCode => $rate) {
                    foreach ($rate as $currencyTo => $value) {
                        $value = abs($this->_objectManager->get(
                            \Magento\Framework\Locale\FormatInterface::class
                        )->getNumber($value));
                        $data[$currencyCode][$currencyTo] = $value;
                        if ($value == 0) {
                            $this->messageManager->addWarning(
                                __('Please correct the input data for "%1 => %2" rate.', $currencyCode, $currencyTo)
                            );
                        }
                    }
                }

                $this->_objectManager->create(\Magento\Directory\Model\Currency::class)->saveRates($data);
                $this->messageManager->addSuccess(__('All valid rates have been saved.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/*/');
    }
}
