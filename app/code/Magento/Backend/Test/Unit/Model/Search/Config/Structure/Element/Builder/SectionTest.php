<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Model\Search\Config\Structure\Element\Builder;

use Magento\Backend\Model\Search\Config\Structure\Element\Builder\Section;
use Magento\Config\Model\Config\Structure\ElementNewInterface;
use PHPUnit\Framework\TestCase;

class SectionTest extends TestCase
{
    /**
     * @var ElementNewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureElementMock;

    /**
     * @var Section
     */
    private $model;

    protected function setUp()
    {
        $this->structureElementMock = $this->getMockForAbstractClass(ElementNewInterface::class);
        $this->model = new Section();
    }

    public function testBuild()
    {
        $structureElementPath = '/path_part_1';

        $this->structureElementMock->expects($this->never())
            ->method('getId');
        $this->structureElementMock->expects($this->once())
            ->method('getPath')
            ->willReturn($structureElementPath);
        $this->assertEquals(['section' => 'path_part_1'], $this->model->build($this->structureElementMock));
    }
}
