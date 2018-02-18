<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Category\Collection;

class UrlRewriteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockBuilder(
                \Magento\Catalog\Model\ResourceModel\Category\Collection::class
            )->disableOriginalConstructor()
            ->setMethodsExcept(['joinUrlRewrite', 'setStoreId', 'getStoreId'])
            ->getMock();
    }


    public function testStoreIdUsedByUrlRewrite()
    {
        $this->_model->expects($this->once())
        ->method('joinTable')
        ->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->equalTo('{{table}}.is_autogenerated = 1 AND {{table}}.store_id = 100 AND {{table}}.entity_type = \'category\''),
            $this->anything()
        );
        $this->_model->setStoreId(100);
        $this->_model->joinUrlRewrite();
    }
}
