<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Backend catalog product grid.
 */
class Grid extends DataGrid
{
    /**
     * Row pattern.
     *
     * @var string
     */
    protected $rowPattern = './/tr[%s]';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => '[name="name"]',
        ],
        'sku' => [
            'selector' => '[name="sku"]',
        ],
        'type' => [
            'selector' => '[name="type_id"]',
            'input' => 'select',
        ],
        'price_from' => [
            'selector' => '[name="price[from]"]',
        ],
        'price_to' => [
            'selector' => '[name="price[to]"]',
        ],
        'qty_from' => [
            'selector' => '[name="qty[from]"]',
        ],
        'qty_to' => [
            'selector' => '[name="qty[to]"]',
        ],
        'visibility' => [
            'selector' => '[name="visibility"]',
            'input' => 'select',
        ],
        'status' => [
            'selector' => '[name="status"]',
            'input' => 'select',
        ],
        'set_name' => [
            'selector' => '[name="attribute_set_id"]',
            'input' => 'select',
        ],
    ];

    /**
     * Product base image.
     *
     * @var string
     */
    protected $baseImage = '.data-grid-thumbnail-cell img';

    /**
     * Update attributes for selected items.
     *
     * @param array $items
     * @return void
     */
    public function updateAttributes(array $items = [])
    {
        $productsSku = [];
        /** @var FixtureInterface $product */
        foreach ($items as $product) {
            $dataConfig = $product->getDataConfig();
            $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;
            if ($this->hasRender($typeId)) {
                $renderArguments = [
                    'product' => $product,
                ];
                $productsSku = $this->callRender($typeId, 'updateAttributes', $renderArguments);
            } else {
                $productsSku[] = ["sku" => $product->getSku()];
            }
        }
        $this->massaction($productsSku, 'Update attributes');
    }

    /**
     * Get base image source link.
     *
     * @return string
     */
    public function getBaseImageSource()
    {
        $baseImage = $this->_rootElement->find($this->baseImage);
        return $baseImage->isVisible() ? $baseImage->getAttribute('src') : '';
    }
}
