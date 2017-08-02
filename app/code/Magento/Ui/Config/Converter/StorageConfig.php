<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Converter;

use Magento\Ui\Config\Converter;
use Magento\Ui\Config\ConverterInterface;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Ui\Config\ConverterUtils;

/**
 * Converter for data provider storage configuration settings
 * @since 2.2.0
 */
class StorageConfig implements ConverterInterface
{
    /**
     * @var ConverterInterface
     * @since 2.2.0
     */
    private $converter;

    /**
     * @var ConverterUtils
     * @since 2.2.0
     */
    private $converterUtils;

    /**
     * @param ConverterInterface $converter
     * @param ConverterUtils $converterUtils
     * @since 2.2.0
     */
    public function __construct(ConverterInterface $converter, ConverterUtils $converterUtils)
    {
        $this->converter = $converter;
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
        if ($node->localName == 'path' || $node->getAttribute(Dom::TYPE_ATTRIBUTE) == 'url') {
            $urlResult = $this->converter->convert($node, ['type' => 'url']);
            return $urlResult ?: [];
        }

        $result = [];
        $result[Converter::NAME_ATTRIBUTE_KEY] = $this->converterUtils->getComponentName($node);

        if ($this->hasChildElements($node)) {
            $result = array_merge($result, $this->processChildNodes($node));
        } else {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                $childResult = [];
                $attributes = [];
                $childResult[Converter::NAME_ATTRIBUTE_KEY] = $this->converterUtils->getComponentName($node);
                $childResult[Dom::TYPE_ATTRIBUTE] = 'string';
                if ($node->hasAttributes()) {
                    foreach ($node->attributes as $attribute) {
                        $attributes[$attribute->nodeName] = $attribute->value;
                    }
                }

                $result = array_merge($childResult, ['value' => trim($node->nodeValue)], $attributes);
            }
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
    private function hasChildElements(\DOMNode $node)
    {
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType == XML_ELEMENT_NODE) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Convert child nodes to array
     *
     * @param \DOMNode $node
     * @return array
     * @since 2.2.0
     */
    private function processChildNodes(\DOMNode $node)
    {
        $result[Dom::TYPE_ATTRIBUTE] = 'array';
        /** @var \DOMNode $childNode */
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE) {
                $result['item'][$this->converterUtils->getComponentName($childNode)] = $this->toArray($childNode);
            }
        }
        return $result;
    }
}
