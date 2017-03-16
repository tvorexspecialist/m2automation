<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Block\Adminhtml\Export\Edit;

use Magento\Mtf\Block\Form as AbstractForm;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Form
 * Export form
 */
class Form extends AbstractForm
{
    /**
     * Form filling.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @param array $attributes
     * @return void
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null, $attributes = [])
    {
        $data = $fixture->getData();
        $fields = isset($data['fields']) ? $data['fields'] : $data;
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                $fields['product'] = [$attribute => $fixture->getDataExport()[$attribute]];
            }
        }
        unset($fields['data_export']);
        $mapping = $this->dataMapping($fields);
        parent::_fill($mapping, $element);
    }
}
