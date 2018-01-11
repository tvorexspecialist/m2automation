<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class InputtypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Model\Product\Attribute\Source\Inputtype */
    protected $inputtypeModel;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    protected function setUp()
    {
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->inputtypeModel = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product\Attribute\Source\Inputtype::class,
            [
                'coreRegistry' => $this->registry
            ]
        );
    }

    public function testToOptionArray()
    {
        $inputTypesSet = [
            ['value' => 'text', 'label' => 'Text Field'],
            ['value' => 'textarea', 'label' => 'Text Area'],
            ['value' => 'texteditor', 'label' => 'Text Editor'],
            ['value' => 'date', 'label' => 'Date'],
            ['value' => 'boolean', 'label' => 'Yes/No'],
            ['value' => 'multiselect', 'label' => 'Multiple Select'],
            ['value' => 'select', 'label' => 'Dropdown'],
            ['value' => 'price', 'label' => 'Price'],
            ['value' => 'media_image', 'label' => 'Media Image'],
        ];

        $this->registry->expects($this->once())->method('registry');
        $this->registry->expects($this->once())->method('register');
        $this->assertEquals($inputTypesSet, $this->inputtypeModel->toOptionArray());
    }
}
