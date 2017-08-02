<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional;

/**
 * Backend grid widget massaction item additional action default
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class DefaultAdditional extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional\AdditionalInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function createFromConfiguration(array $configuration)
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        foreach ($configuration as $itemId => $item) {
            $item['class'] = isset($item['class']) ? $item['class'] . ' absolute-advice' : 'absolute-advice';
            $form->addField($itemId, $item['type'], $item);
        }
        $this->setForm($form);
        return $this;
    }
}
