<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form note element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Class \Magento\Framework\Data\Form\Element\Note
 *
 * @since 2.0.0
 */
class Note extends AbstractElement
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('note');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getElementHtml()
    {
        $html = $this->getBeforeElementHtml()
            . '<div id="'
            . $this->getHtmlId()
            . '" class="control-value admin__field-value">'
            . $this->getText()
            . '</div>'
            . $this->getAfterElementHtml();
        return $html;
    }
}
