<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorFactory;
use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorInterface;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacade;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Config\Model\Config\PathValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\ConfigurationMismatchException;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test for ProcessorFacade.
 *
 * @see ProcessorFacade
 */
class ProcessorFacadeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessorFacade
     */
    private $model;

    /**
     * @var ValidatorInterface|Mock
     */
    private $scopeValidatorMock;

    /**
     * @var PathValidator|Mock
     */
    private $pathValidatorMock;

    /**
     * @var ConfigSetProcessorFactory|Mock
     */
    private $configSetProcessorFactoryMock;

    /**
     * @var ConfigSetProcessorInterface|Mock
     */
    private $processorMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->scopeValidatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $this->pathValidatorMock = $this->getMockBuilder(PathValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configSetProcessorFactoryMock = $this->getMockBuilder(ConfigSetProcessorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorMock = $this->getMockBuilder(ConfigSetProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->configSetProcessorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->processorMock);

        $this->model = new ProcessorFacade(
            $this->scopeValidatorMock,
            $this->pathValidatorMock,
            $this->configSetProcessorFactoryMock
        );
    }

    public function testProcess()
    {
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->pathValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_DEFAULT)
            ->willReturn($this->processorMock);
        $this->processorMock->expects($this->once())
            ->method('process')
            ->with('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);

        $this->assertSame(
            'Value was saved.',
            $this->model->process('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, false)
        );
    }

    /**
     * @param LocalizedException $exception
     * @dataProvider processWithValidatorExceptionDataProvider
     */
    public function testProcessWithValidatorException(LocalizedException $exception)
    {
        $this->setExpectedException(ValidatorException::class, 'Some error');
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willThrowException($exception);

        $this->model->process('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, false);
    }

    /**
     * @return array
     */
    public function processWithValidatorExceptionDataProvider()
    {
        return [
            [new LocalizedException(__('Some error'))],
            [new ValidatorException(__('Some error'))],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\ConfigurationMismatchException
     * @expectedExceptionMessage Some error
     */
    public function testProcessWithConfigurationMismatchException()
    {
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->pathValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_DEFAULT)
            ->willThrowException(new ConfigurationMismatchException(__('Some error')));
        $this->processorMock->expects($this->never())
            ->method('process');

        $this->model->process('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, false);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Some error
     */
    public function testProcessWithCouldNotSaveException()
    {
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->pathValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_DEFAULT)
            ->willReturn($this->processorMock);
        $this->processorMock->expects($this->once())
            ->method('process')
            ->with('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)
            ->willThrowException(new CouldNotSaveException(__('Some error')));

        $this->model->process('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, false);
    }

    public function testExecuteLock()
    {
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_LOCK)
            ->willReturn($this->processorMock);
        $this->processorMock->expects($this->once())
            ->method('process')
            ->with('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);

        $this->assertSame(
            'Value was saved and locked.',
            $this->model->process('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, true)
        );
    }
}
