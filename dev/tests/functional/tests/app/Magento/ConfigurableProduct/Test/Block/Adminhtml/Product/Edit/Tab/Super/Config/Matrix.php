<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config;

use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Template;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Matrix
 * Matrix row form
 */
class Matrix extends Form
{
    /**
     * Mapping for get optional fields
     *
     * @var array
     */
    protected $mappingGetFields = [
        'name' => [
            'selector' => 'td[data-column="name"] > a',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'sku' => [
            'selector' => 'td[data-column="sku"] > span',
            'strategy' => Locator::SELECTOR_CSS,
        ],
        'quantity_and_stock_status' => [
            'composite' => 1,
            'fields' => [
                'qty' => [
                    'selector' => 'td[data-column="qty"]',
                    'strategy' => Locator::SELECTOR_CSS,
                ],
            ],
        ],
        'weight' => [
            'selector' => 'td[data-column="weight"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
    ];

    /**
     * Selector for variation row by number
     *
     * @var string
     */
    protected $variationRowByNumber = './/tr[@data-role="row"][%d]';

    /**
     * Selector for variation row
     *
     * @var string
     */
    protected $variationRow = './/tr[@data-role="row"]';

    /**
     * Button for assign product to variation
     *
     * @var string
     */
    protected $configurableAttribute = 'td[data-column="name"] button.action-choose';

    // @codingStandardsIgnoreStart
    /**
     * Selector for row on product grid by product id
     *
     * @var string
     */
    protected $selectAssociatedProduct = '//ancestor::div[*[@id="associated-products-container"]]//td[@data-column="entity_id" and (contains(text(),"%s"))]';
    // @codingStandardsIgnoreEnd

    /**
     * Title of variation matrix css selector.
     *
     * @var string
     */
    protected $matrixTitle = 'h3';

    /**
     * Selector for template block.
     *
     * @var string
     */
    protected $template = './ancestor::body';

    /**
     * Fill variations.
     *
     * @param array $matrix
     * @return void
     */
    public function fillVariations(array $matrix)
    {
        $this->_rootElement->find($this->matrixTitle)->click();
        $count = 1;
        foreach ($matrix as $variation) {
            $variationRow = $this->_rootElement->find(
                sprintf($this->variationRowByNumber, $count),
                Locator::SELECTOR_XPATH
            );
            ksort($variation);
            $mapping = $this->dataMapping($variation);

            $this->_fill($mapping, $variationRow);
            if (isset($variation['configurable_attribute'])) {
                $this->assignProduct($variationRow, $variation['configurable_attribute']);
            }

            ++$count;
        }
    }

    /**
     * Fill form data.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return void
     * @throws \Exception
     */
    protected function _fill(array $fields, SimpleElement $element = null)
    {
        $context = ($element === null) ? $this->_rootElement : $element;
        foreach ($fields as $name => $field) {
            if (!isset($field['value'])) {
                $this->_fill($field, $context);
            } else {
                $element = $this->getElement($context, $field);
                if (!$element->isVisible()) {
                    continue;
                }

                if (!$element->isDisabled()) {
                    $element->setValue($field['value']);
                } else {
                    throw new \Exception("Unable to set value to field '$name' as it's disabled.");
                }
            }
        }
    }

    /**
     * Assign product to variation matrix
     *
     * @param SimpleElement $variationRow
     * @param int $productId
     * @return void
     */
    protected function assignProduct(SimpleElement $variationRow, $productId)
    {
        $variationRow->find($this->configurableAttribute)->click();
        $this->getTemplateBlock()->waitLoader();
        $this->_rootElement->find(
            sprintf($this->selectAssociatedProduct, $productId),
            Locator::SELECTOR_XPATH
        )->click();
    }

    /**
     * Get variations data
     *
     * @return array
     */
    public function getVariationsData()
    {
        $data = [];
        $variationRows = $this->_rootElement->getElements($this->variationRow, Locator::SELECTOR_XPATH);

        foreach ($variationRows as $key => $variationRow) {
            /** @var SimpleElement $variationRow */
            if ($variationRow->isVisible()) {
                $data[$key] = $this->_getData($this->dataMapping(), $variationRow);
                $data[$key] += $this->getOptionalFields($variationRow, $this->mappingGetFields);
            }
        }

        return $data;
    }

    /**
     * Get optional fields
     *
     * @param SimpleElement $context
     * @param array $fields
     * @return array
     */
    protected function getOptionalFields(SimpleElement $context, array $fields)
    {
        $data = [];

        foreach ($fields as $name => $params) {
            if (isset($params['composite']) && $params['composite']) {
                $data[$name] = $this->getOptionalFields($context, $params['fields']);
            } else {
                $data[$name] = $context->find($params['selector'], $params['strategy'])->getText();
            }
        }
        return $data;
    }

    /**
     * Get template block.
     *
     * @return Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->template, Locator::SELECTOR_XPATH)]
        );
    }
}
