<?php
/**
 * Service Input Processor
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi;

use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\SerializationException;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\Exception as WebapiException;
use Zend\Code\Reflection\ClassReflection;

/**
 * Deserialize arguments from API requests.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 */
class ServiceInputProcessor implements ServicePayloadConverterInterface
{
    const EXTENSION_ATTRIBUTES_TYPE = \Magento\Framework\Api\ExtensionAttributesInterface::class;

    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    protected $typeProcessor;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory
     */
    protected $attributeValueFactory;

    /**
     * @var \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface
     */
    protected $customAttributeTypeLocator;

    /**
     * @var \Magento\Framework\Reflection\MethodsMap
     */
    protected $methodsMap;

    /**
     * @var \Magento\Framework\Reflection\NameFinder
     */
    private $nameFinder;

    /**
     * @var array
     */
    private $serviceTypeToEntityTypeMap;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Initialize dependencies.
     *
     * @param TypeProcessor $typeProcessor
     * @param ObjectManagerInterface $objectManager
     * @param AttributeValueFactory $attributeValueFactory
     * @param CustomAttributeTypeLocatorInterface $customAttributeTypeLocator
     * @param MethodsMap $methodsMap
     * @param ConfigInterface $config
     * @param ServiceTypeToEntityTypeMap $serviceTypeToEntityTypeMap
     */
    public function __construct(
        TypeProcessor $typeProcessor,
        ObjectManagerInterface $objectManager,
        AttributeValueFactory $attributeValueFactory,
        CustomAttributeTypeLocatorInterface $customAttributeTypeLocator,
        MethodsMap $methodsMap,
        ConfigInterface $config = null,
        ServiceTypeToEntityTypeMap $serviceTypeToEntityTypeMap = null
    ) {
        $this->typeProcessor = $typeProcessor;
        $this->objectManager = $objectManager;
        $this->attributeValueFactory = $attributeValueFactory;
        $this->customAttributeTypeLocator = $customAttributeTypeLocator;
        $this->methodsMap = $methodsMap;
        $this->serviceTypeToEntityTypeMap = $serviceTypeToEntityTypeMap
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(ServiceTypeToEntityTypeMap::class);
        $this->config = $config
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(ConfigInterface::class);
    }

    /**
     * The getter function to get the new NameFinder dependency
     *
     * @return \Magento\Framework\Reflection\NameFinder
     *
     * @deprecated 100.1.0
     */
    private function getNameFinder()
    {
        if ($this->nameFinder === null) {
            $this->nameFinder = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Reflection\NameFinder::class);
        }
        return $this->nameFinder;
    }

    /**
     * Convert the input array from key-value format to a list of parameters suitable for the specified class / method.
     *
     * The input array should have the field name as the key, and the value will either be a primitive or another
     * key-value array.  The top level of this array needs keys that match the names of the parameters on the
     * service method.
     *
     * Mismatched types are caught by the PHP runtime, not explicitly checked for by this code.
     *
     * @param string $serviceClassName name of the service class that we are trying to call
     * @param string $serviceMethodName name of the method that we are trying to call
     * @param array $inputArray data to send to method in key-value format
     * @return array list of parameters that can be used to call the service method
     * @throws InputException if no value is provided for required parameters
     * @throws WebapiException
     */
    public function process($serviceClassName, $serviceMethodName, array $inputArray)
    {
        $inputData = [];
        $inputError = [];
        foreach ($this->methodsMap->getMethodParams($serviceClassName, $serviceMethodName) as $param) {
            $paramName = $param[MethodsMap::METHOD_META_NAME];
            $snakeCaseParamName = strtolower(preg_replace("/(?<=\\w)(?=[A-Z])/", "_$1", $paramName));
            if (isset($inputArray[$paramName]) || isset($inputArray[$snakeCaseParamName])) {
                $paramValue = isset($inputArray[$paramName])
                    ? $inputArray[$paramName]
                    : $inputArray[$snakeCaseParamName];

                try {
                    $inputData[] = $this->convertValue($paramValue, $param[MethodsMap::METHOD_META_TYPE]);
                } catch (SerializationException $e) {
                    throw new WebapiException(new Phrase($e->getMessage()));
                }
            } else {
                if ($param[MethodsMap::METHOD_META_HAS_DEFAULT_VALUE]) {
                    $inputData[] = $param[MethodsMap::METHOD_META_DEFAULT_VALUE];
                } else {
                    $inputError[] = $paramName;
                }
            }
        }
        $this->processInputError($inputError);
        return $inputData;
    }

    /**
     * @param string $className
     * @param array $data
     * @return array
     * @throws \ReflectionException
     */
    private function getConstructorData(string $className, array $data): array
    {
        $preferenceClass = $this->config->getPreference($className);
        $class = new ClassReflection($preferenceClass ?: $className);

        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $res = [];
        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            if (isset($data[$parameter->getName()])) {
                $res[$parameter->getName()] = $data[$parameter->getName()];
            }
        }

        return $res;
    }

    /**
     * Creates a new instance of the given class and populates it with the array of data. The data can
     * be in different forms depending on the adapter being used, REST vs. SOAP. For REST, the data is
     * in snake_case (e.g. tax_class_id) while for SOAP the data is in camelCase (e.g. taxClassId).
     *
     * @param string $className
     * @param array $data
     * @return object the newly created and populated object
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _createFromArray($className, $data)
    {
        $data = is_array($data) ? $data : [];
        // convert to string directly to avoid situations when $className is object
        // which implements __toString method like \ReflectionObject
        $className = (string) $className;
        $class = new ClassReflection($className);
        if (is_subclass_of($className, self::EXTENSION_ATTRIBUTES_TYPE)) {
            $className = substr($className, 0, -strlen('Interface'));
        }

        // Primary method: assign to constructor parameters
        $constructorArgs = $this->getConstructorData($className, $data);
        $object = $this->objectManager->create($className, $constructorArgs);

        // Secondary method: fallback to setter methods
        foreach ($data as $propertyName => $value) {
            if (isset($constructorArgs[$propertyName])) {
                continue;
            }

            // Converts snake_case to uppercase CamelCase to help form getter/setter method names
            // This use case is for REST only. SOAP request data is already camel cased
            $camelCaseProperty = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($propertyName);
            $methodName = $this->getNameFinder()->getGetterMethodName($class, $camelCaseProperty);
            $methodReflection = $class->getMethod($methodName);
            if ($methodReflection->isPublic()) {
                $returnType = $this->typeProcessor->getGetterReturnType($methodReflection)['type'];
                try {
                    $setterName = $this->getNameFinder()->getSetterMethodName($class, $camelCaseProperty);
                } catch (\Exception $e) {
                    if (empty($value)) {
                        continue;
                    } else {
                        throw $e;
                    }
                }
                try {
                    if ($camelCaseProperty === 'CustomAttributes') {
                        $setterValue = $this->convertCustomAttributeValue($value, $className);
                    } else {
                        $setterValue = $this->convertValue($value, $returnType);
                    }
                } catch (SerializationException $e) {
                    throw new SerializationException(
                        new Phrase(
                            'Error occurred during "%field_name" processing. %details',
                            ['field_name' => $propertyName, 'details' => $e->getMessage()]
                        )
                    );
                }
                $object->{$setterName}($setterValue);
            }
        }
        return $object;
    }

    /**
     * Convert custom attribute data array to array of AttributeValue Data Object
     *
     * @param array $customAttributesValueArray
     * @param string $dataObjectClassName
     * @return AttributeValue[]
     * @throws SerializationException
     */
    protected function convertCustomAttributeValue($customAttributesValueArray, $dataObjectClassName)
    {
        $result = [];
        $dataObjectClassName = ltrim($dataObjectClassName, '\\');

        foreach ($customAttributesValueArray as $key => $customAttribute) {
            if (!is_array($customAttribute)) {
                $customAttribute = [AttributeValue::ATTRIBUTE_CODE => $key, AttributeValue::VALUE => $customAttribute];
            }

            list($customAttributeCode, $customAttributeValue) = $this->processCustomAttribute($customAttribute);

            $entityType = $this->serviceTypeToEntityTypeMap->getEntityType($dataObjectClassName);
            if ($entityType) {
                $type = $this->customAttributeTypeLocator->getType(
                    $customAttributeCode,
                    $entityType
                );
            } else {
                $type = TypeProcessor::ANY_TYPE;
            }

            if ($this->typeProcessor->isTypeAny($type) || $this->typeProcessor->isTypeSimple($type)
                || !is_array($customAttributeValue)
            ) {
                try {
                    $attributeValue = $this->convertValue($customAttributeValue, $type);
                } catch (SerializationException $e) {
                    throw new SerializationException(
                        new Phrase(
                            'Attribute "%attribute_code" has invalid value. %details',
                            ['attribute_code' => $customAttributeCode, 'details' => $e->getMessage()]
                        )
                    );
                }
            } else {
                $attributeValue = $this->_createDataObjectForTypeAndArrayValue($type, $customAttributeValue);
            }

            //Populate the attribute value data object once the value for custom attribute is derived based on type
            $result[$customAttributeCode] = $this->attributeValueFactory->create()
                ->setAttributeCode($customAttributeCode)
                ->setValue($attributeValue);
        }

        return $result;
    }

    /**
     * Derive the custom attribute code and value.
     *
     * @param string[] $customAttribute
     * @return string[]
     */
    private function processCustomAttribute($customAttribute)
    {
        $camelCaseAttributeCodeKey = lcfirst(
            SimpleDataObjectConverter::snakeCaseToUpperCamelCase(AttributeValue::ATTRIBUTE_CODE)
        );
        // attribute code key could be snake or camel case, depending on whether SOAP or REST is used.
        if (isset($customAttribute[AttributeValue::ATTRIBUTE_CODE])) {
            $customAttributeCode = $customAttribute[AttributeValue::ATTRIBUTE_CODE];
        } elseif (isset($customAttribute[$camelCaseAttributeCodeKey])) {
            $customAttributeCode = $customAttribute[$camelCaseAttributeCodeKey];
        } else {
            $customAttributeCode = null;
        }

        if (!$customAttributeCode && !isset($customAttribute[AttributeValue::VALUE])) {
            throw new SerializationException(
                new Phrase('An empty custom attribute is specified. Enter the attribute and try again.')
            );
        } elseif (!$customAttributeCode) {
            throw new SerializationException(
                new Phrase(
                    'A custom attribute is specified with a missing attribute code. Verify the code and try again.'
                )
            );
        } elseif (!array_key_exists(AttributeValue::VALUE, $customAttribute)) {
            throw new SerializationException(
                new Phrase(
                    'The "' . $customAttributeCode .
                    '" attribute code doesn\'t have a value set. Enter the value and try again.'
                )
            );
        }

        return [$customAttributeCode, $customAttribute[AttributeValue::VALUE]];
    }

    /**
     * Creates a data object type from a given type name and a PHP array.
     *
     * @param string $type The type of data object to create
     * @param array $customAttributeValue The data object values
     * @return mixed
     */
    protected function _createDataObjectForTypeAndArrayValue($type, $customAttributeValue)
    {
        if (substr($type, -2) === "[]") {
            $type = substr($type, 0, -2);
            $attributeValue = [];
            foreach ($customAttributeValue as $value) {
                $attributeValue[] = $this->_createFromArray($type, $value);
            }
        } else {
            $attributeValue = $this->_createFromArray($type, $customAttributeValue);
        }

        return $attributeValue;
    }

    /**
     * Convert data from array to Data Object representation if type is Data Object or array of Data Objects.
     *
     * @param mixed $data
     * @param string $type Convert given value to the this type
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function convertValue($data, $type)
    {
        $isArrayType = $this->typeProcessor->isArrayType($type);
        if ($isArrayType && isset($data['item'])) {
            $data = $this->_removeSoapItemNode($data);
        }
        if ($this->typeProcessor->isTypeSimple($type) || $this->typeProcessor->isTypeAny($type)) {
            $result = $this->typeProcessor->processSimpleAndAnyType($data, $type);
        } else {
            /** Complex type or array of complex types */
            if ($isArrayType) {
                // Initializing the result for array type else it will return null for empty array
                $result = is_array($data) ? [] : null;
                $itemType = $this->typeProcessor->getArrayItemType($type);
                if (is_array($data)) {
                    foreach ($data as $key => $item) {
                        $result[$key] = $this->_createFromArray($itemType, $item);
                    }
                }
            } else {
                $result = $this->_createFromArray($type, $data);
            }
        }
        return $result;
    }

    /**
     * Remove item node added by the SOAP server for array types
     *
     * @param array|mixed $value
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _removeSoapItemNode($value)
    {
        if (isset($value['item'])) {
            if (is_array($value['item'])) {
                $value = $value['item'];
            } else {
                return [$value['item']];
            }
        } else {
            throw new \InvalidArgumentException('Value must be an array and must contain "item" field.');
        }
        /**
         * In case when only one Data object value is passed, it will not be wrapped into a subarray
         * within item node. If several Data object values are passed, they will be wrapped into
         * an indexed array within item node.
         */
        $isAssociative = array_keys($value) !== range(0, count($value) - 1);
        return $isAssociative ? [$value] : $value;
    }

    /**
     * Process an input error
     *
     * @param array $inputError
     * @return void
     * @throws InputException
     */
    protected function processInputError($inputError)
    {
        if (!empty($inputError)) {
            $exception = new InputException();
            foreach ($inputError as $errorParamField) {
                $exception->addError(
                    new Phrase('"%fieldName" is required. Enter and try again.', ['fieldName' => $errorParamField])
                );
            }
            if ($exception->wasErrorAdded()) {
                throw $exception;
            }
        }
    }
}
