<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogWidget\Test\Unit\Model;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\CatalogWidget\Model\Rule\Condition\CombineFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $combineFactory;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->combineFactory = $this->getMockBuilder(\Magento\CatalogWidget\Model\Rule\Condition\CombineFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->mockObjectManager([
            \Magento\Framework\Api\ExtensionAttributesFactory::class =>
                $this->getMock(\Magento\Framework\Api\ExtensionAttributesFactory::class, [], [], '', false),
            \Magento\Framework\Api\AttributeValueFactory::class =>
                $this->getMock(\Magento\Framework\Api\AttributeValueFactory::class, [], [], '', false)
        ]);

        $this->rule = $this->objectManager->getObject(
            \Magento\CatalogWidget\Model\Rule::class,
            [
                'conditionsFactory' => $this->combineFactory
            ]
        );
    }

    protected function tearDown()
    {
        $this->objectManager->restoreObjectManager();
    }

    public function testGetConditionsInstance()
    {
        $condition = $this->getMockBuilder(\Magento\CatalogWidget\Model\Rule\Condition\Combine::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->combineFactory->expects($this->once())->method('create')->will($this->returnValue($condition));
        $this->assertSame($condition, $this->rule->getConditionsInstance());
    }

    public function testGetActionsInstance()
    {
        $this->assertNull($this->rule->getActionsInstance());
    }
}
