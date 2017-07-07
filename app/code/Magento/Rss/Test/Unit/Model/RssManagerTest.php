<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Rss\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RssManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rss\Model\RssManager
     */
    protected $rssManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->rssManager = $objectManagerHelper->getObject(
            \Magento\Rss\Model\RssManager::class,
            [
                'objectManager' => $this->objectManager,
                'dataProviders' => [
                    'rss_feed' => \Magento\Framework\App\Rss\DataProviderInterface::class,
                    'bad_rss_feed' => 'Some\Class\Not\Existent',
                ]
            ]
        );
    }

    public function testGetProvider()
    {
        $dataProvider = $this->getMock(\Magento\Framework\App\Rss\DataProviderInterface::class);
        $this->objectManager->expects($this->once())->method('get')->will($this->returnValue($dataProvider));

        $this->assertInstanceOf(
             \Magento\Framework\App\Rss\DataProviderInterface::class,
             $this->rssManager->getProvider('rss_feed')
        );
    }

    public function testGetProviderFirstException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->rssManager->getProvider('wrong_rss_feed');
    }

    public function testGetProviderSecondException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->rssManager->getProvider('bad_rss_feed');
    }
}
