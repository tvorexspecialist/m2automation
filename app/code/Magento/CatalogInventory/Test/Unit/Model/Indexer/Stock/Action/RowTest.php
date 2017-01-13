<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @subpackage  unit_tests
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock\Action;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows
     */
    protected $_model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(\Magento\CatalogInventory\Model\Indexer\Stock\Action\Row::class);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We can't rebuild the index for an undefined product.
     */
    public function testEmptyId()
    {
        $this->_model->execute(null);
    }
}
