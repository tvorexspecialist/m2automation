<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Data;
use Magento\Config\Model\Config\Structure\Element\FlyweightFactory;
use Magento\Config\Model\Config\Structure\Element\Iterator\Tab as TabIterator;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test for Structure.
 *
 * @see Structure
 */
class StructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Structure|Mock
     */
    protected $_model;

    /**
     * @var FlyweightFactory|Mock
     */
    protected $_flyweightFactory;

    /**
     * @var TabIterator|Mock
     */
    protected $_tabIteratorMock;

    /**
     * @var Data|Mock
     */
    protected $_structureDataMock;

    /**
     * @var ScopeDefiner|Mock
     */
    protected $_scopeDefinerMock;

    /**
     * @var array
     */
    protected $_structureData;

    protected function setUp()
    {
<<<<<<< HEAD
        $this->_flyweightFactory = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\FlyweightFactory::class,
            [],
            [],
            '',
            false
        );
        $this->_tabIteratorMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\Iterator\Tab::class,
            [],
            [],
            '',
            false
        );
        $this->_structureDataMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Data::class,
            [],
            [],
            '',
            false
        );
        $this->_scopeDefinerMock = $this->getMock(
            \Magento\Config\Model\Config\ScopeDefiner::class,
            [],
            [],
            '',
            false
        );
        $this->_scopeDefinerMock->expects($this->any())->method('getScope')->will($this->returnValue('scope'));

        $filePath = dirname(__DIR__) . '/_files';
        $this->_structureData = require $filePath . '/converted_config.php';
        $this->_structureDataMock->expects(
            $this->once()
        )->method(
            'get'
        )->will(
            $this->returnValue($this->_structureData['config']['system'])
        );
        $this->_model = new \Magento\Config\Model\Config\Structure(
=======
        $this->_flyweightFactory = $this->getMockBuilder(FlyweightFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_tabIteratorMock = $this->getMockBuilder(TabIterator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_structureDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_scopeDefinerMock = $this->getMockBuilder(ScopeDefiner::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_structureData = require dirname(__DIR__) . '/_files/converted_config.php';

        $this->_scopeDefinerMock->expects($this->any())
            ->method('getScope')
            ->willReturn('scope');
        $this->_structureDataMock->expects($this->once())
            ->method('get')
            ->willReturn($this->_structureData['config']['system']);

        $this->_model = new Structure(
>>>>>>> mainline/develop
            $this->_structureDataMock,
            $this->_tabIteratorMock,
            $this->_flyweightFactory,
            $this->_scopeDefinerMock
        );
    }

    public function testGetTabsBuildsSectionTree()
    {
<<<<<<< HEAD
        $this->_structureDataMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Data::class,
            [],
            [],
            '',
            false
        );
        $this->_structureDataMock->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValue(
                ['sections' => ['section1' => ['tab' => 'tab1']], 'tabs' => ['tab1' => []]]
            )
        );
=======
>>>>>>> mainline/develop
        $expected = ['tab1' => ['children' => ['section1' => ['tab' => 'tab1']]]];

        $this->_structureDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_structureDataMock->expects($this->any())
            ->method('get')
            ->willReturn(
                ['sections' => ['section1' => ['tab' => 'tab1']], 'tabs' => ['tab1' => []]]
            );
        $this->_tabIteratorMock->expects($this->once())
            ->method('setElements')
            ->with($expected);

        $model = new \Magento\Config\Model\Config\Structure(
            $this->_structureDataMock,
            $this->_tabIteratorMock,
            $this->_flyweightFactory,
            $this->_scopeDefinerMock
        );

        $this->assertEquals($this->_tabIteratorMock, $model->getTabs());
    }

    public function testGetSectionList()
    {
<<<<<<< HEAD
        $this->_structureDataMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Data::class,
            [],
            [],
            '',
            false
        );
        $this->_structureDataMock->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValue(
=======
        $expected = [
            'section1_child_id_1' => true,
            'section1_child_id_2' => true,
            'section1_child_id_3' => true,
            'section2_child_id_1' => true
        ];

        $this->_structureDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_structureDataMock->expects($this->any())
            ->method('get')
            ->willReturn(
>>>>>>> mainline/develop
                [
                    'sections' => [
                        'section1' => [
                            'children' => [
                                'child_id_1' => 'child_data',
                                'child_id_2' => 'child_data',
                                'child_id_3' => 'child_data'
                            ]
                        ],
                        'section2' => [
                            'children' => [
                                'child_id_1' => 'child_data'
                            ]
                        ],
                    ]
                ]
            );

        $model = new \Magento\Config\Model\Config\Structure(
            $this->_structureDataMock,
            $this->_tabIteratorMock,
            $this->_flyweightFactory,
            $this->_scopeDefinerMock
        );

        $this->assertEquals($expected, $model->getSectionList());
    }

    /**
     * @param string $path
     * @param string $expectedType
     * @param string $expectedId
     * @param string $expectedPath
     * @dataProvider emptyElementDataProvider
     */
    public function testGetElementReturnsEmptyElementIfNotExistingElementIsRequested(
        $path,
        $expectedType,
        $expectedId,
        $expectedPath
    ) {
        $expectedConfig = ['id' => $expectedId, 'path' => $expectedPath, '_elementType' => $expectedType];

        $elementMock = $this->getMockBuilder(Structure\ElementInterface::class)
            ->getMockForAbstractClass();
        $elementMock->expects($this->once())
            ->method('setData')
            ->with($expectedConfig);
        $this->_flyweightFactory->expects($this->once())
            ->method('create')
            ->with($expectedType)
            ->willReturn($elementMock);

        $this->assertEquals($elementMock, $this->_model->getElement($path));
    }

    public function emptyElementDataProvider()
    {
        return [
            ['someSection/group_1/nonexisting_field', 'field', 'nonexisting_field', 'someSection/group_1'],
            ['section_1/group_1/nonexisting_field', 'field', 'nonexisting_field', 'section_1/group_1'],
            ['section_1/nonexisting_group', 'group', 'nonexisting_group', 'section_1'],
            ['nonexisting_section', 'section', 'nonexisting_section', '']
        ];
    }

    public function testGetElementReturnsProperElementByPath()
    {
<<<<<<< HEAD
        $elementMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\Field::class,
            [],
            [],
            '',
            false
        );
=======
>>>>>>> mainline/develop
        $section = $this->_structureData['config']['system']['sections']['section_1'];
        $fieldData = $section['children']['group_level_1']['children']['field_3'];

        $elementMock = $this->getMockBuilder(Structure\Element\Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $elementMock->expects($this->once())
            ->method('setData')
            ->with($fieldData, 'scope');
        $this->_flyweightFactory->expects($this->once())
            ->method('create')
            ->with('field')
            ->willReturn($elementMock);

        $this->assertEquals($elementMock, $this->_model->getElement('section_1/group_level_1/field_3'));
    }

    public function testGetElementByPathPartsIfSectionDataIsEmpty()
    {
<<<<<<< HEAD
        $elementMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\Field::class,
            [],
            [],
            '',
            false
        );
=======
>>>>>>> mainline/develop
        $fieldData = [
            'id' => 'field_3',
            'path' => 'section_1/group_level_1',
            '_elementType' => 'field',
        ];
        $pathParts = explode('/', 'section_1/group_level_1/field_3');

<<<<<<< HEAD
        $structureDataMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Data::class,
            [],
            [],
            '',
            false
        );
=======
        $elementMock = $this->getMockBuilder(Structure\Element\Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $structureDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
>>>>>>> mainline/develop

        $elementMock->expects($this->once())
            ->method('setData')
            ->with($fieldData, 'scope');
        $this->_flyweightFactory->expects($this->once())
            ->method('create')
            ->with('field')
            ->willReturn($elementMock);
        $structureDataMock->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $structureMock = new Structure(
            $structureDataMock,
            $this->_tabIteratorMock,
            $this->_flyweightFactory,
            $this->_scopeDefinerMock
        );

        $this->assertEquals($elementMock, $structureMock->getElementByPathParts($pathParts));
    }

    public function testGetFirstSectionReturnsFirstAllowedSection()
    {
<<<<<<< HEAD
        $tabMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\Tab::class,
            ['current', 'getChildren', 'rewind'],
            [],
            '',
            false
        );
        $tabMock->expects($this->any())->method('getChildren')->will($this->returnSelf());
        $tabMock->expects($this->once())->method('rewind');
        $tabMock->expects($this->once())->method('current')->will($this->returnValue('currentSection'));
        $this->_tabIteratorMock->expects($this->once())->method('rewind');
        $this->_tabIteratorMock->expects($this->once())->method('current')->will($this->returnValue($tabMock));
=======
        $tabMock = $this->getMockBuilder(Structure\Element\Tab::class)
            ->disableOriginalConstructor()
            ->setMethods(['current', 'getChildren', 'rewind'])
            ->getMock();

        $tabMock->expects($this->any())
            ->method('getChildren')
            ->willReturnSelf();
        $tabMock->expects($this->once())
            ->method('rewind');
        $tabMock->expects($this->once())
            ->method('current')
            ->willReturn('currentSection');
        $this->_tabIteratorMock->expects($this->once())
            ->method('rewind');
        $this->_tabIteratorMock->expects($this->once())
            ->method('current')
            ->willReturn($tabMock);

>>>>>>> mainline/develop
        $this->assertEquals('currentSection', $this->_model->getFirstSection());
    }

    public function testGetElementReturnsProperElementByPathCachesObject()
    {
<<<<<<< HEAD
        $elementMock = $this->getMock(
            \Magento\Config\Model\Config\Structure\Element\Field::class,
            [],
            [],
            '',
            false
        );
=======
>>>>>>> mainline/develop
        $section = $this->_structureData['config']['system']['sections']['section_1'];
        $fieldData = $section['children']['group_level_1']['children']['field_3'];

        $elementMock = $this->getMockBuilder(Structure\Element\Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $elementMock->expects($this->once())
            ->method('setData')
            ->with($fieldData, 'scope');
        $this->_flyweightFactory->expects($this->once())
            ->method('create')
            ->with('field')
            ->willReturn($elementMock);

        $this->assertEquals($elementMock, $this->_model->getElement('section_1/group_level_1/field_3'));
        $this->assertEquals($elementMock, $this->_model->getElement('section_1/group_level_1/field_3'));
    }

    /**
     * @param $attributeName
     * @param $attributeValue
     * @param $paths
     * @dataProvider getFieldPathsByAttributeDataProvider
     */
    public function testGetFieldPathsByAttribute($attributeName, $attributeValue, $paths)
    {
        $this->assertEquals($paths, $this->_model->getFieldPathsByAttribute($attributeName, $attributeValue));
    }

    public function getFieldPathsByAttributeDataProvider()
    {
        return [
            [
                'backend_model',
                \Magento\Config\Model\Config\Backend\Encrypted::class,
                [
                    'section_1/group_1/field_2',
                    'section_1/group_level_1/group_level_2/group_level_3/field_3_1_1',
                    'section_2/group_3/field_4',
                ]
            ],
            ['attribute_2', 'test_value_2', ['section_2/group_3/field_4']]
        ];
    }

    public function testGetFieldPaths()
    {
        $expected = [
            'section/group/field2' => [
                'field_2'
            ],
            'field_3' => [
                'field_3'
            ],
            'field_3_1' => [
                'field_3_1'
            ],
            'field_3_1_1' => [
                'field_3_1_1'
            ],
            'section/group/field4' => [
                'field_4',
            ],
            'field_5' => [
                'field_5',
            ],
        ];

        $this->assertSame(
            $expected,
            $this->_model->getFieldPaths()
        );
    }
}
