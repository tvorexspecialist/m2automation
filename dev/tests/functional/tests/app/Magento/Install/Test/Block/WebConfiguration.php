<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Web configuration block.
 */
class WebConfiguration extends Form
{
    /**
     * 'Next' button.
     *
     * @var string
     */
    protected $next = "[ng-click*='next']";

    /**
     * 'Advanced Options' locator.
     *
     * @var string
     */
    protected $advancedOptions = "[ng-click*='advanced']";

    /**
     * Fill web configuration form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $data = $fixture->getData();
        $webConfiguration = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'db') !== 0 && strpos($key, 'store') !== 0) {
                $webConfiguration[$key] = $value;
            }
        }
        $mapping = $this->dataMapping($webConfiguration);
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Click on 'Next' button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next)->click();
    }

    /**
     * Click on 'Advanced Options' button.
     *
     * @return void
     */
    public function clickAdvancedOptions()
    {
        $this->_rootElement->find($this->advancedOptions)->click();
    }
}
