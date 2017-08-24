<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Widget;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea frontend
 */
class GenderTest extends \PHPUnit\Framework\TestCase
{
    /** @var Gender */
    protected $_block;

    /**
     * Test initialization and set up. Create the Gender block.
     * @return void
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
        $this->_block = $objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Customer\Block\Widget\Gender::class
        );
    }

    /**
     * Test the Gender::getGenderOptions() method.
     * @return void
     */
    public function testGetGenderOptions()
    {
        $options = $this->_block->getGenderOptions();
        $this->assertInternalType('array', $options);
        $this->assertNotEmpty($options);
        $this->assertContainsOnlyInstancesOf(\Magento\Customer\Model\Data\Option::class, $options);
    }

    /**
     * Test the Gender::toHtml() method.
     * @return void
     */
    public function testToHtml()
    {
        $html = $this->_block->toHtml();
        $this->assertContains('<span>Gender</span>', $html);
        $this->assertContains('<option value="1">Male</option>', $html);
        $this->assertContains('<option value="2">Female</option>', $html);
        $this->assertContains('<option value="3">Not Specified</option>', $html);
    }
}
