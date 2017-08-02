<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Converter;

use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Ui\Config\Converter;
use Magento\Ui\Config\ConverterInterface;
use Magento\Ui\Config\ConverterUtils;

/**
 * Converter for 'settings/deps' configuration settings
 * @since 2.2.0
 */
class Deps implements ConverterInterface
{
    /**
     * @var ConverterUtils
     * @since 2.2.0
     */
    private $converterUtils;

    /**
     * @param ConverterUtils $converterUtils
     * @since 2.2.0
     */
    public function __construct(ConverterUtils $converterUtils)
    {
        $this->converterUtils = $converterUtils;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function convert(\DOMNode $node, array $data = [])
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return [];
        }
        return $this->toArray($node);
    }

    /**
     * Convert nodes and child nodes to array
     *
     * @param \DOMNode $node
     * @return array
     * @since 2.2.0
     */
    private function toArray(\DOMNode $node)
    {
        $result = [
            'name' => $this->converterUtils->getComponentName($node),
            Dom::TYPE_ATTRIBUTE => 'array'
        ];
        if ($this->hasChildNodes($node)) {
            $i = 0;
            /** @var \DOMNode $childNode */
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType === XML_ELEMENT_NODE) {
                    $result['item'][] = [
                        'name' => $i,
                        Dom::TYPE_ATTRIBUTE => 'string',
                        'value' => trim($childNode->nodeValue)
                    ];
                    $i++;
                }
            }
        } else {
            $result['item'][] = [
                'name' => 0,
                Dom::TYPE_ATTRIBUTE => 'string',
                'value' => trim($node->nodeValue)
            ];
        }
        return $result;
    }

    /**
     * Check is DOMNode has child DOMElements
     *
     * @param \DOMNode $node
     * @return bool
     * @since 2.2.0
     */
    private function hasChildNodes(\DOMNode $node)
    {
        if (!$node->hasChildNodes()) {
            return false;
        }

        foreach ($node->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                return true;
            }
        }

        return false;
    }
}
