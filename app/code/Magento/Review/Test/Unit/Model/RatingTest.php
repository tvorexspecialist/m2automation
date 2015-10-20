<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Review\Model\Rating;

class RatingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Review\Model\Rating
     */
    private $rating;

    /**
     * Init objects needed by tests
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->rating = $helper->getObject(Rating::class);
    }

    /**
     * @covers \Magento\Review\Model\Rating::getIdentities()
     * @return void
     */
    public function testGetIdentities()
    {
        static::assertEquals([Store::CACHE_TAG], $this->rating->getIdentities());
    }
}
