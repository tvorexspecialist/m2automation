<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Helper class for basic object retrieving, such as blocks, models etc...
 */
namespace Magento\Framework\TestFramework\Unit\Helper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class ObjectManager
{
    /**
     * Special cases configuration
     *
     * @var array
     * @since 2.0.0
     */
    protected $_specialCases = [
        \Magento\Framework\Model\ResourceModel\AbstractResource::class => '_getResourceModelMock',
        \Magento\Framework\TranslateInterface::class => '_getTranslatorMock',
    ];

    /**
     * Test object
     *
     * @var \PHPUnit_Framework_TestCase
     * @since 2.0.0
     */
    protected $_testObject;

    /**
     * Class constructor
     *
     * @param \PHPUnit_Framework_TestCase $testObject
     * @since 2.0.0
     */
    public function __construct(\PHPUnit_Framework_TestCase $testObject)
    {
        $this->_testObject = $testObject;
    }

    /**
     * Get mock for argument
     *
     * @param string $argClassName
     * @param array $originalArguments
     * @return null|object|\PHPUnit_Framework_MockObject_MockObject
     * @since 2.0.0
     */
    protected function _createArgumentMock($argClassName, array $originalArguments)
    {
        $object = null;
        if ($argClassName) {
            $object = $this->_processSpecialCases($argClassName, $originalArguments);
            if (null === $object) {
                $object = $this->_getMockWithoutConstructorCall($argClassName);
            }
        }
        return $object;
    }

    /**
     * Process special cases
     *
     * @param string $className
     * @param array $arguments
     * @return null|object
     * @since 2.0.0
     */
    protected function _processSpecialCases($className, $arguments)
    {
        $object = null;
        $interfaces = class_implements($className);
        if (in_array(\Magento\Framework\ObjectManager\ContextInterface::class, $interfaces)) {
            $object = $this->getObject($className, $arguments);
        } elseif (isset($this->_specialCases[$className])) {
            $method = $this->_specialCases[$className];
            $object = $this->{$method}($className);
        }

        return $object;
    }

    /**
     * Retrieve specific mock of core resource model
     *
     * @return \Magento\Framework\Module\ResourceInterface|\PHPUnit_Framework_MockObject_MockObject
     * @since 2.0.0
     */
    protected function _getResourceModelMock()
    {
        $resourceMock = $this->_testObject->getMock(
            \Magento\Framework\Module\ModuleResource::class,
            ['getIdFieldName', '__sleep', '__wakeup'],
            [],
            '',
            false
        );
        $resourceMock->expects(
            $this->_testObject->any()
        )->method(
            'getIdFieldName'
        )->will(
            $this->_testObject->returnValue('id')
        );

        return $resourceMock;
    }

    /**
     * Retrieve mock of core translator model
     *
     * @param string $className
     * @return \Magento\Framework\TranslateInterface|\PHPUnit_Framework_MockObject_MockObject
     * @since 2.0.0
     */
    protected function _getTranslatorMock($className)
    {
        $translator = $this->_testObject->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $translateCallback = function ($arguments) {
            return is_array($arguments) ? vsprintf(array_shift($arguments), $arguments) : '';
        };
        $translator->expects(
            $this->_testObject->any()
        )->method(
            'translate'
        )->will(
            $this->_testObject->returnCallback($translateCallback)
        );
        return $translator;
    }

    /**
     * Get mock without call of original constructor
     *
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     * @since 2.0.0
     */
    protected function _getMockWithoutConstructorCall($className)
    {
        $class = new \ReflectionClass($className);
        $mock = null;
        if ($class->isAbstract()) {
            $mock = $this->_testObject->getMockForAbstractClass($className, [], '', false, false);
        } else {
            $mock = $this->_testObject->getMock($className, [], [], '', false, false);
        }
        return $mock;
    }

    /**
     * Get class instance
     *
     * @param string $className
     * @param array $arguments
     * @return object
     * @since 2.0.0
     */
    public function getObject($className, array $arguments = [])
    {
        if (is_subclass_of($className, \Magento\Framework\Api\AbstractSimpleObjectBuilder::class)
            || is_subclass_of($className, \Magento\Framework\Api\Builder::class)
        ) {
            return $this->getBuilder($className, $arguments);
        }
        $constructArguments = $this->getConstructArguments($className, $arguments);
        $reflectionClass = new \ReflectionClass($className);
        $newObject = $reflectionClass->newInstanceArgs($constructArguments);

        foreach (array_diff_key($arguments, $constructArguments) as $key => $value) {
            $propertyReflectionClass = $reflectionClass;
            while ($propertyReflectionClass) {
                if ($propertyReflectionClass->hasProperty($key)) {
                    $reflectionProperty = $propertyReflectionClass->getProperty($key);
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($newObject, $value);
                    break;
                }
                $propertyReflectionClass = $propertyReflectionClass->getParentClass();
            }
        }
        return $newObject;
    }

    /**
     * Get data object builder
     *
     * @param string $className
     * @param array $arguments
     * @return object
     * @since 2.0.0
     */
    protected function getBuilder($className, array $arguments)
    {
        if (!isset($arguments['objectFactory'])) {
            $objectFactory =
                $this->_testObject->getMock(\Magento\Framework\Api\ObjectFactory::class, [], [], '', false);

            $objectFactory->expects($this->_testObject->any())
                ->method('populateWithArray')
                ->will($this->_testObject->returnSelf());
            $objectFactory->expects($this->_testObject->any())
                ->method('populate')
                ->will($this->_testObject->returnSelf());
            $objectFactory->expects($this->_testObject->any())
                ->method('create')
                ->will($this->_testObject->returnCallback(
                    function ($className, $arguments) {
                        $reflectionClass = new \ReflectionClass($className);
                        $constructorMethod = $reflectionClass->getConstructor();
                        $parameters = $constructorMethod->getParameters();
                        $args = [];
                        foreach ($parameters as $parameter) {
                            $parameterName = $parameter->getName();
                            if (isset($arguments[$parameterName])) {
                                $args[] = $arguments[$parameterName];
                            } else {
                                if ($parameter->isArray()) {
                                    $args[] = [];
                                } elseif ($parameter->allowsNull()) {
                                    $args[] = null;
                                } else {
                                    $mock = $this->_getMockWithoutConstructorCall($parameter->getClass()->getName());
                                    $args[] = $mock;
                                }
                            }
                        }
                        return new $className(...array_values($args));
                    }
                ));

            $arguments['objectFactory'] = $objectFactory;
        }

        return new $className(...array_values($this->getConstructArguments($className, $arguments)));
    }

    /**
     * Retrieve associative array of arguments that used for new object instance creation
     *
     * @param string $className
     * @param array $arguments
     * @return array
     * @since 2.0.0
     */
    public function getConstructArguments($className, array $arguments = [])
    {
        $constructArguments = [];
        if (!method_exists($className, '__construct')) {
            return $constructArguments;
        }
        $method = new \ReflectionMethod($className, '__construct');

        foreach ($method->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $argClassName = null;
            $defaultValue = null;

            if (array_key_exists($parameterName, $arguments)) {
                $constructArguments[$parameterName] = $arguments[$parameterName];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $defaultValue = $parameter->getDefaultValue();
            }

            try {
                if ($parameter->getClass()) {
                    $argClassName = $parameter->getClass()->getName();
                }
                $object = $this->_getMockObject($argClassName, $arguments);
            } catch (\ReflectionException $e) {
                $parameterString = $parameter->__toString();
                $firstPosition = strpos($parameterString, '<required>');
                if ($firstPosition !== false) {
                    $parameterString = substr($parameterString, $firstPosition + 11);
                    $parameterString = substr($parameterString, 0, strpos($parameterString, ' '));
                    $object = $this->_testObject->getMock($parameterString, [], [], '', false);
                }
            }

            $constructArguments[$parameterName] = null === $object ? $defaultValue : $object;
        }
        return $constructArguments;
    }

    /**
     * Get collection mock
     *
     * @param string $className
     * @param array $data
     * @return \PHPUnit_Framework_MockObject_MockObject
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function getCollectionMock($className, array $data)
    {
        if (!is_subclass_of($className, \Magento\Framework\Data\Collection::class)) {
            throw new \InvalidArgumentException(
                $className . ' does not instance of \Magento\Framework\Data\Collection'
            );
        }
        $mock = $this->_testObject->getMock($className, [], [], '', false, false);
        $iterator = new \ArrayIterator($data);
        $mock->expects(
            $this->_testObject->any()
        )->method(
            'getIterator'
        )->will(
            $this->_testObject->returnValue($iterator)
        );
        return $mock;
    }

    /**
     * Helper function that creates a mock object for a given class name.
     *
     * Will return a real object in some cases to assist in testing.
     *
     * @param string $argClassName
     * @param array $arguments
     * @return null|object|\PHPUnit_Framework_MockObject_MockObject
     * @since 2.0.0
     */
    private function _getMockObject($argClassName, array $arguments)
    {
        if (is_subclass_of($argClassName, \Magento\Framework\Api\ExtensibleObjectBuilder::class)) {
            $object = $this->getBuilder($argClassName, $arguments);
            return $object;
        } else {
            $object = $this->_createArgumentMock($argClassName, $arguments);
            return $object;
        }
    }

    /**
     * Set mocked property
     *
     * @param object $object
     * @param string $propertyName
     * @param object $propertyValue
     * @param string $className The namespace of parent class for injection private property into this class
     * @return void
     * @since 2.1.0
     */
    public function setBackwardCompatibleProperty($object, $propertyName, $propertyValue, $className = '')
    {
        $reflection = new \ReflectionClass($className ? $className : get_class($object));
        $reflectionProperty = $reflection->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $propertyValue);
    }
}
