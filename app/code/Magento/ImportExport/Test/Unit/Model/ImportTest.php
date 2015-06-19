<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model;

/**
 * Class ImportTest
 * @package Magento\ImportExport\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ImportTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Entity adapter.
     *
     * @var \Magento\ImportExport\Model\Import\Entity\AbstractEntity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityAdapter;

    /**
     * Import export data
     *
     * @var \Magento\ImportExport\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importExportData = null;

    /**
     * @var \Magento\ImportExport\Model\Import\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importConfig;

    /**
     * @var \Magento\ImportExport\Model\Import\Entity\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityFactory;

    /**
     * @var \Magento\ImportExport\Model\Resource\Import\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importData;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\CsvFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_csvFactory;

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_httpFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\Indexer\Model\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\Behavior\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_behaviorFactory;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreConfig;

    /**
     * @var \Magento\ImportExport\Model\Import|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $import;

    /**
     * @var \Magento\ImportExport\Model\History|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_varDirectory;

    public function setUp()
    {
        $logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_filesystem = $this->getMockBuilder('\Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_importExportData = $this->getMockBuilder('\Magento\ImportExport\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_coreConfig = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_importConfig = $this->getMockBuilder('\Magento\ImportExport\Model\Import\ConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getEntityTypeCode', 'getBehavior'])
            ->getMockForAbstractClass();
        $this->_entityFactory = $this->getMockBuilder('\Magento\ImportExport\Model\Import\Entity\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_importData = $this->getMockBuilder('\Magento\ImportExport\Model\Resource\Import\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_csvFactory = $this->getMockBuilder('\Magento\ImportExport\Model\Export\Adapter\CsvFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_httpFactory = $this->getMockBuilder('\Magento\Framework\HTTP\Adapter\FileTransferFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_uploaderFactory = $this->getMockBuilder('\Magento\MediaStorage\Model\File\UploaderFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_behaviorFactory = $this->getMockBuilder('\Magento\ImportExport\Model\Source\Import\Behavior\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerRegistry = $this->getMockBuilder('\Magento\Indexer\Model\IndexerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyModel = $this->getMockBuilder('\Magento\ImportExport\Model\History')
            ->disableOriginalConstructor()
            ->setMethods([
                'updateReport',
                'invalidateReport',
                'addReport',
            ])
            ->getMock();
        $this->historyModel->expects($this->any())->method('updateReport')->willReturnSelf();
        $this->dateTime = $this->getMockBuilder('\Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_varDirectory = $this->getMockBuilder('\Magento\Framework\Filesystem\Directory\WriteInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->import = $this->getMockBuilder('\Magento\ImportExport\Model\Import')
            ->setConstructorArgs([
                $logger,
                $this->_filesystem,
                $this->_importExportData,
                $this->_coreConfig,
                $this->_importConfig,
                $this->_entityFactory,
                $this->_importData,
                $this->_csvFactory,
                $this->_httpFactory,
                $this->_uploaderFactory,
                $this->_behaviorFactory,
                $this->indexerRegistry,
                $this->historyModel,
                $this->dateTime
            ])
            ->setMethods([
                'getDataSourceModel',
                '_getEntityAdapter',
                'setData',
                'getProcessedEntitiesCount',
                'getProcessedRowsCount',
                'getInvalidRowsCount',
                'getErrorsCount',
                'getEntity',
                'getBehavior',
                'isReportEntityType',
            ])
            ->getMock();

        $this->setPropertyValue($this->import, '_varDirectory', $this->_varDirectory);

        $this->_entityAdapter = $this->getMockBuilder('\Magento\ImportExport\Model\Import\Entity\AbstractEntity')
            ->disableOriginalConstructor()
            ->setMethods(['importData'])
            ->getMockForAbstractClass();

    }

    /**
     * Test importSource()
     */
    public function testImportSource()
    {
        $entityTypeCode = 'code';
        $this->_importData->expects($this->any())
                        ->method('getEntityTypeCode')
                        ->will($this->returnValue($entityTypeCode));
        $behaviour = 'behaviour';
        $this->_importData->expects($this->once())
                        ->method('getBehavior')
                        ->will($this->returnValue($behaviour));
        $this->import->expects($this->any())
                    ->method('getDataSourceModel')
                    ->will($this->returnValue($this->_importData));

        $this->import->expects($this->any())->method('setData')->withConsecutive(
            ['entity', $entityTypeCode],
            ['behavior', $behaviour]
        );
        $phraseClass = '\Magento\Framework\Phrase';
        $this->import->expects($this->any())
                    ->method('addLogComment')
                    ->with($this->isInstanceOf($phraseClass));
        $this->_entityAdapter->expects($this->once())
                    ->method('importData')
                    ->will($this->returnSelf());
        $this->import->expects($this->once())
                    ->method('_getEntityAdapter')
                    ->will($this->returnValue($this->_entityAdapter));

        $importOnceMethodsReturnNull = [
            'getEntity',
            'getBehavior',
            'getProcessedRowsCount',
            'getProcessedEntitiesCount',
            'getInvalidRowsCount',
            'getErrorsCount',
        ];

        foreach ($importOnceMethodsReturnNull as $method) {
            $this->import->expects($this->once())->method($method)->will($this->returnValue(null));
        }

        $this->import->importSource();
    }

    /**
     * Test importSource with expected exception
     *
     * @expectedException \Magento\Framework\Exception\AlreadyExistsException
     * @expectedExceptionMessage URL key for specified store already exists.
     */
    public function testImportSourceException()
    {
        $exceptionMock = new \Magento\Framework\Exception\AlreadyExistsException(
            __('URL key for specified store already exists.')
        );
        $entityTypeCode = 'code';
        $this->_importData->expects($this->any())
            ->method('getEntityTypeCode')
            ->will($this->returnValue($entityTypeCode));
        $behaviour = 'behaviour';
        $this->_importData->expects($this->once())
            ->method('getBehavior')
            ->will($this->returnValue($behaviour));
        $this->import->expects($this->any())
            ->method('getDataSourceModel')
            ->will($this->returnValue($this->_importData));
        $this->import->expects($this->any())->method('setData')->withConsecutive(
            ['entity', $entityTypeCode],
            ['behavior', $behaviour]
        );
        $phraseClass = '\Magento\Framework\Phrase';
        $this->import->expects($this->any())
            ->method('addLogComment')
            ->with($this->isInstanceOf($phraseClass));
        $this->_entityAdapter->expects($this->once())
            ->method('importData')
            ->will($this->throwException($exceptionMock));
        $this->import->expects($this->once())
            ->method('_getEntityAdapter')
            ->will($this->returnValue($this->_entityAdapter));
        $importOnceMethodsReturnNull = [
            'getEntity',
            'getBehavior',
        ];

        foreach ($importOnceMethodsReturnNull as $method) {
            $this->import->expects($this->once())->method($method)->will($this->returnValue(null));
        }

        $this->import->importSource();
    }

    /**
     * @todo to implement it.
     */
    public function testGetOperationResultMessages()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetAttributeType()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetEntity()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetErrorsCount()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetErrorsLimit()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetInvalidRowsCount()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetNotices()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetProcessedEntitiesCount()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetProcessedRowsCount()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetWorkingDir()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testIsImportAllowed()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testUploadSource()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testValidateSource()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testInvalidateIndex()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetEntityBehaviors()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo to implement it.
     */
    public function testGetUniqueEntityBehaviors()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * Cover isReportEntityType().
     *
     * @dataProvider isReportEntityTypeDataProvider
     */
    public function testIsReportEntityType($entity, $processedReportsEntities, $getEntityResult, $expectedResult)
    {
        $importMock = $this->getMockBuilder('\Magento\ImportExport\Model\Import')
            ->disableOriginalConstructor()
            ->setMethods([
                'getEntity',
            ])
            ->getMock();

        $this->setPropertyValue($importMock, 'processedReportsEntities', $processedReportsEntities);
        $importMock
            ->expects($this->any())
            ->method('getEntity')
            ->willReturn($getEntityResult);

        $actualResult = $importMock->isReportEntityType($entity);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Cover createHistoryReport().
     */
    public function testCreateHistoryReportEmptyReportEntityType()
    {
        $sourceFileRelative = 'sourceFileRelative';
        $entity = 'entity val';
        $extension = null;
        $result = null;

        $this->import
            ->expects($this->once())
            ->method('isReportEntityType')
            ->with($entity)
            ->willReturn(false);
        $this->_varDirectory
            ->expects($this->never())
            ->method('getRelativePath');
        $this->_varDirectory
            ->expects($this->never())
            ->method('copyFile');
        $this->dateTime
            ->expects($this->never())
            ->method('gmtTimestamp');
        $this->historyModel
            ->expects($this->never())
            ->method('addReport');

        $args = [
            $sourceFileRelative,
            $entity,
            $extension,
            $result
        ];
        $actualResult = $this->invokeMethod($this->import, 'createHistoryReport', $args);
        $this->assertEquals($this->import, $actualResult);
    }

    /**
     * Cover createHistoryReport().
     */
    public function testCreateHistoryReportSourceFileRelativeIsArray()
    {
        $sourceFileRelative = [
            'file_name' => 'sourceFileRelative value',
        ];
        $sourceFileRelativeNew = 'sourceFileRelative new value';
        $entity = '';
        $extension = null;
        $result = '';
        $fileName = $sourceFileRelative['file_name'];
        $gmtTimestamp = 1234567;
        $copyName = $gmtTimestamp . '_' . $fileName;
        $copyFile = \Magento\ImportExport\Model\Import::IMPORT_HISTORY_DIR . $copyName;

        $this->import
            ->expects($this->once())
            ->method('isReportEntityType')
            ->with($entity)
            ->willReturn(true);
        $this->_varDirectory
            ->expects($this->once())
            ->method('getRelativePath')
            ->with(\Magento\ImportExport\Model\Import::IMPORT_DIR . $fileName)
            ->willReturn($sourceFileRelativeNew);
        $this->_varDirectory
            ->expects($this->once())
            ->method('copyFile')
            ->with(
                $sourceFileRelativeNew,
                $copyFile
            );
        $this->dateTime
            ->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($gmtTimestamp);
        $this->historyModel
            ->expects($this->once())
            ->method('addReport')
            ->with($copyName);

        $args = [
            $sourceFileRelative,
            $entity,
            $extension,
            $result
        ];
        $actualResult = $this->invokeMethod($this->import, 'createHistoryReport', $args);
        $this->assertEquals($this->import, $actualResult);
    }

    /**
     * Cover createHistoryReport().
     */
    public function testCreateHistoryReportSourceFileRelativeIsNotArrayResultIsSet()
    {
        $sourceFileRelative = 'not array';
        $entity = '';
        $extension = null;
        $result = [
            'name' => 'result value',
        ];
        $fileName = $result['name'];
        $gmtTimestamp = 1234567;
        $copyName = $gmtTimestamp . '_' . $fileName;
        $copyFile = \Magento\ImportExport\Model\Import::IMPORT_HISTORY_DIR . $copyName;

        $this->import
            ->expects($this->once())
            ->method('isReportEntityType')
            ->with($entity)
            ->willReturn(true);
        $this->_varDirectory
            ->expects($this->never())
            ->method('getRelativePath');
        $this->_varDirectory
            ->expects($this->once())
            ->method('copyFile')
            ->with(
                $sourceFileRelative,
                $copyFile
            );
        $this->dateTime
            ->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($gmtTimestamp);
        $this->historyModel
            ->expects($this->once())
            ->method('addReport')
            ->with($copyName);

        $args = [
            $sourceFileRelative,
            $entity,
            $extension,
            $result
        ];
        $actualResult = $this->invokeMethod($this->import, 'createHistoryReport', $args);
        $this->assertEquals($this->import, $actualResult);
    }

    /**
     * Cover createHistoryReport().
     */
    public function testCreateHistoryReportExtensionIsSet()
    {
        $sourceFileRelative = 'not array';
        $entity = 'entity value';
        $extension = 'extension value';
        $result = [];
        $fileName = $entity . $extension;
        $gmtTimestamp = 1234567;
        $copyName = $gmtTimestamp . '_' . $fileName;
        $copyFile = \Magento\ImportExport\Model\Import::IMPORT_HISTORY_DIR . $copyName;

        $this->import
            ->expects($this->once())
            ->method('isReportEntityType')
            ->with($entity)
            ->willReturn(true);
        $this->_varDirectory
            ->expects($this->never())
            ->method('getRelativePath');
        $this->_varDirectory
            ->expects($this->once())
            ->method('copyFile')
            ->with(
                $sourceFileRelative,
                $copyFile
            );
        $this->dateTime
            ->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($gmtTimestamp);
        $this->historyModel
            ->expects($this->once())
            ->method('addReport')
            ->with($copyName);

        $args = [
            $sourceFileRelative,
            $entity,
            $extension,
            $result
        ];
        $actualResult = $this->invokeMethod($this->import, 'createHistoryReport', $args);
        $this->assertEquals($this->import, $actualResult);
    }

    /**
     * Cover createHistoryReport().
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Source file coping failed
     */
    public function testCreateHistoryReportThrowException()
    {
        $sourceFileRelative = null;
        $entity = '';
        $extension = '';
        $result = '';
        $gmtTimestamp = 1234567;

        $this->import
            ->expects($this->once())
            ->method('isReportEntityType')
            ->with($entity)
            ->willReturn(true);
        $this->_varDirectory
            ->expects($this->never())
            ->method('getRelativePath');
        $phrase = $this->getMock('\Magento\Framework\Phrase', [], [], '', false);
        $this->_varDirectory
            ->expects($this->once())
            ->method('copyFile')
            ->willReturnCallback(function () use ($phrase) {
                throw new \Magento\Framework\Exception\FileSystemException($phrase);
            });
        $this->dateTime
            ->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($gmtTimestamp);
        $this->historyModel
            ->expects($this->never())
            ->method('addReport');

        $args = [
            $sourceFileRelative,
            $entity,
            $extension,
            $result
        ];
        $actualResult = $this->invokeMethod($this->import, 'createHistoryReport', $args);
        $this->assertEquals($this->import, $actualResult);
    }

    public function isReportEntityTypeDataProvider()
    {
        return [
            [
                '$entity' => null,
                '$processedReportsEntities' => [],
                '$getEntityResult' => null,
                '$expectedResult' => false,
            ],
            [
                '$entity' => null,
                '$processedReportsEntities' => [
                    'entity'
                ],
                '$getEntityResult' => null,
                '$expectedResult' => false,
            ],
            [
                '$entity' => null,
                '$processedReportsEntities' => [
                    'entity 1'
                ],
                '$getEntityResult' => 'entity 2',
                '$expectedResult' => false,
            ],
            [
                '$entity' => 'entity',
                '$processedReportsEntities' => [
                    'entity 1'
                ],
                '$getEntityResult' => 'entity 2',
                '$expectedResult' => false,
            ],
            [
                '$entity' => 'entity',
                '$processedReportsEntities' => [
                    'entity'
                ],
                '$getEntityResult' => null,
                '$expectedResult' => true,
            ],
        ];
    }

    /**
     * Set property for an object.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }

    /**
     * Invoke any method of an object.
     *
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
