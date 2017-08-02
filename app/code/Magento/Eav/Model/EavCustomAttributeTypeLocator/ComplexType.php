<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\EavCustomAttributeTypeLocator;

/**
 * Class to locate complex types for EAV custom attributes
 * @since 2.1.0
 */
class ComplexType
{
    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     * @since 2.1.0
     */
    private $stringUtility;

    /**
     * Initialize dependencies
     *
     * @codeCoverageIgnore
     * @param \Magento\Framework\Stdlib\StringUtils $stringUtility
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $stringUtility
    ) {
        $this->stringUtility = $stringUtility;
    }

    /**
     * Get attribute type based on its backend model.
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param string $serviceClass
     * @param $serviceBackendModelDataInterfaceMap array
     * @return string|null
     * @since 2.1.0
     */
    public function getType($attribute, $serviceClass, $serviceBackendModelDataInterfaceMap)
    {
        $backendModel = $attribute->getBackendModel();
        //If empty backend model, check if it can be derived
        if (empty($backendModel)) {
            $backendModelClass = sprintf(
                'Magento\Eav\Model\Attribute\Data\%s',
                $this->stringUtility->upperCaseWords($attribute->getFrontendInput())
            );
            $backendModel = class_exists($backendModelClass) ? $backendModelClass : null;
        }

        $dataInterface = isset($serviceBackendModelDataInterfaceMap[$serviceClass][$backendModel])
            ? $serviceBackendModelDataInterfaceMap[$serviceClass][$backendModel]
            : null;

        return $dataInterface;
    }
}
