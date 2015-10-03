<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Unit\Model;


class ObserverTest extends \PHPUnit_Framework_TestCase
{
    public function testChangeTemplate()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Event\Observer $observer */
        $observer = $this->getMock('\Magento\Framework\Event\Observer', [], [], '', false);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject
         * |\Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo $block
         */
        $block = $this->getMock('\Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo', [], [], '', false);
        $block->expects($this->once())
              ->method('setTemplate')
              ->with('Magento_ProductVideo::helper/gallery.phtml')
              ->willReturnSelf();
        $observer->expects($this->once())->method('__call')->with('getBlock')->willReturn($block);

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ProductVideo\Model\Observer $model */
        $model = $this->getMock('\Magento\ProductVideo\Model\Observer', null, [], '', false);
        $model->changeTemplate($observer);
    }
}
