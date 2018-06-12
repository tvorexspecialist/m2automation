<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Ui\Component\Form;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Data provider for main panel of product page
 *
 * @api
 * @since 101.0.0
 */
class General extends AbstractModifier
{
    /**
     * @var LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * @var ArrayManager
     * @since 101.0.0
     */
    protected $arrayManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param AttributeRepositoryInterface|null $attributeRepository
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        AttributeRepositoryInterface $attributeRepository = null
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->attributeRepository = $attributeRepository
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(AttributeRepositoryInterface::class);
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function modifyData(array $data)
    {
        $data = $this->customizeWeightFormat($data);
        $data = $this->customizeAdvancedPriceFormat($data);
        $modelId = $this->locator->getProduct()->getId();

        if (!isset($data[$modelId][static::DATA_SOURCE_DEFAULT][ProductAttributeInterface::CODE_STATUS])) {
            $attributeStatus = $this->attributeRepository->get(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                ProductAttributeInterface::CODE_STATUS
            );
            $data[$modelId][static::DATA_SOURCE_DEFAULT][ProductAttributeInterface::CODE_STATUS] =
                $attributeStatus->getDefaultValue() ?: 1;
        }

        return $data;
    }

    /**
     * Customizing weight fields
     *
     * @param array $data
     * @return array
     * @since 101.0.0
     */
    protected function customizeWeightFormat(array $data)
    {
        $model = $this->locator->getProduct();
        $modelId = $model->getId();
        $weightFields = [ProductAttributeInterface::CODE_WEIGHT];

        foreach ($weightFields as $fieldCode) {
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/' . $fieldCode;
            $data = $this->arrayManager->replace(
                $path,
                $data,
                $this->formatWeight($this->arrayManager->get($path, $data))
            );
        }

        return $data;
    }

    /**
     * Customizing number fields for advanced price
     *
     * @param array $data
     * @return array
     * @since 101.0.0
     */
    protected function customizeAdvancedPriceFormat(array $data)
    {
        $modelId = $this->locator->getProduct()->getId();
        $fieldCode = ProductAttributeInterface::CODE_TIER_PRICE;

        if (isset($data[$modelId][self::DATA_SOURCE_DEFAULT][$fieldCode])) {
            foreach ($data[$modelId][self::DATA_SOURCE_DEFAULT][$fieldCode] as &$value) {
                $value[ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE] =
                    $this->formatPrice($value[ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE]);
                $value[ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE_QTY] =
                    (float) $value[ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE_QTY];
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->prepareFirstPanel($meta);
        $meta = $this->customizeStatusField($meta);
        $meta = $this->customizeWeightField($meta);
        $meta = $this->customizeNewDateRangeField($meta);
        $meta = $this->customizeNameListeners($meta);

        return $meta;
    }

    /**
     * Disable collapsible and set empty label
     *
     * @param array $meta
     * @return array
     * @since 101.0.0
     */
    protected function prepareFirstPanel(array $meta)
    {
        if ($generalPanelCode = $this->getFirstPanelCode($meta)) {
            $meta[$generalPanelCode] = $this->arrayManager->merge(
                'arguments/data/config',
                $meta[$generalPanelCode],
                [
                    'label' => '',
                    'collapsible' => false,
                ]
            );
        }

        return $meta;
    }

    /**
     * Customize Status field
     *
     * @param array $meta
     * @return array
     * @since 101.0.0
     */
    protected function customizeStatusField(array $meta)
    {
        $switcherConfig = [
            'dataType' => Form\Element\DataType\Number::NAME,
            'formElement' => Form\Element\Checkbox::NAME,
            'componentType' => Form\Field::NAME,
            'prefer' => 'toggle',
            'valueMap' => [
                'true' => '1',
                'false' => '2'
            ],
        ];

        $path = $this->arrayManager->findPath(ProductAttributeInterface::CODE_STATUS, $meta, null, 'children');
        $meta = $this->arrayManager->merge($path . static::META_CONFIG_PATH, $meta, $switcherConfig);

        return $meta;
    }

    /**
     * Customize Weight filed
     *
     * @param array $meta
     * @return array
     * @since 101.0.0
     */
    protected function customizeWeightField(array $meta)
    {
        $weightPath = $this->arrayManager->findPath(ProductAttributeInterface::CODE_WEIGHT, $meta, null, 'children');

        if ($weightPath) {
            $meta = $this->arrayManager->merge(
                $weightPath . static::META_CONFIG_PATH,
                $meta,
                [
                    'dataScope' => ProductAttributeInterface::CODE_WEIGHT,
                    'validation' => [
                        'validate-zero-or-greater' => true
                    ],
                    'additionalClasses' => 'admin__field-small',
                    'addafter' => $this->locator->getStore()->getConfig('general/locale/weight_unit'),
                    'imports' => [
                        'disabled' => '!${$.provider}:' . self::DATA_SCOPE_PRODUCT
                            . '.product_has_weight:value'
                    ]
                ]
            );

            $containerPath = $this->arrayManager->findPath(
                static::CONTAINER_PREFIX . ProductAttributeInterface::CODE_WEIGHT,
                $meta,
                null,
                'children'
            );
            $meta = $this->arrayManager->merge($containerPath . static::META_CONFIG_PATH, $meta, [
                'component' => 'Magento_Ui/js/form/components/group',
            ]);

            $hasWeightPath = $this->arrayManager->slicePath($weightPath, 0, -1) . '/'
                . ProductAttributeInterface::CODE_HAS_WEIGHT;
            $meta = $this->arrayManager->set(
                $hasWeightPath . static::META_CONFIG_PATH,
                $meta,
                [

                    'dataType' => 'boolean',
                    'formElement' => Form\Element\Select::NAME,
                    'componentType' => Form\Field::NAME,
                    'dataScope' => 'product_has_weight',
                    'label' => '',
                    'options' => [
                        [
                            'label' => __('This item has weight'),
                            'value' => 1
                        ],
                        [
                            'label' => __('This item has no weight'),
                            'value' => 0
                        ],
                    ],
                    'value' => (int)$this->locator->getProduct()->getTypeInstance()->hasWeight(),
                ]
            );
        }

        return $meta;
    }

    /**
     * Customize "Set Product as New" date fields
     *
     * @param array $meta
     * @return array
     * @since 101.0.0
     */
    protected function customizeNewDateRangeField(array $meta)
    {
        $fromField = 'news_from_date';
        $toField = 'news_to_date';

        $fromFieldPath = $this->arrayManager->findPath($fromField, $meta, null, 'children');
        $toFieldPath = $this->arrayManager->findPath($toField, $meta, null, 'children');

        if ($fromFieldPath && $toFieldPath) {
            $fromContainerPath = $this->arrayManager->slicePath($fromFieldPath, 0, -2);
            $toContainerPath = $this->arrayManager->slicePath($toFieldPath, 0, -2);

            $meta = $this->arrayManager->merge(
                $fromFieldPath . self::META_CONFIG_PATH,
                $meta,
                [
                    'label' => __('Set Product as New From'),
                    'additionalClasses' => 'admin__field-date',
                ]
            );
            $meta = $this->arrayManager->merge(
                $toFieldPath . self::META_CONFIG_PATH,
                $meta,
                [
                    'label' => __('To'),
                    'scopeLabel' => null,
                    'additionalClasses' => 'admin__field-date',
                ]
            );
            $meta = $this->arrayManager->merge(
                $fromContainerPath . self::META_CONFIG_PATH,
                $meta,
                [
                    'label' => __('Set Product as New From'),
                    'additionalClasses' => 'admin__control-grouped-date',
                    'breakLine' => false,
                    'component' => 'Magento_Ui/js/form/components/group',
                ]
            );
            $meta = $this->arrayManager->set(
                $fromContainerPath . '/children/' . $toField,
                $meta,
                $this->arrayManager->get($toFieldPath, $meta)
            );

            $meta = $this->arrayManager->remove($toContainerPath, $meta);
        }

        return $meta;
    }

    /**
     * Add links for fields depends of product name
     *
     * @param array $meta
     * @return array
     * @since 101.0.0
     */
    protected function customizeNameListeners(array $meta)
    {
        $listeners = [
            ProductAttributeInterface::CODE_SKU,
            ProductAttributeInterface::CODE_SEO_FIELD_META_TITLE,
            ProductAttributeInterface::CODE_SEO_FIELD_META_KEYWORD,
            ProductAttributeInterface::CODE_SEO_FIELD_META_DESCRIPTION,
        ];
        $textListeners = [
            ProductAttributeInterface::CODE_SEO_FIELD_META_KEYWORD,
            ProductAttributeInterface::CODE_SEO_FIELD_META_DESCRIPTION
        ];

        foreach ($listeners as $listener) {
            $listenerPath = $this->arrayManager->findPath($listener, $meta, null, 'children');
            $importsConfig = [
                'mask' => $this->locator->getStore()->getConfig('catalog/fields_masks/' . $listener),
                'component' => 'Magento_Catalog/js/components/import-handler',
                'allowImport' => !$this->locator->getProduct()->getId(),
            ];

            if (!in_array($listener, $textListeners)) {
                $importsConfig['elementTmpl'] = 'ui/form/element/input';
            }

            $meta = $this->arrayManager->merge($listenerPath . static::META_CONFIG_PATH, $meta, $importsConfig);
        }

        $skuPath = $this->arrayManager->findPath(ProductAttributeInterface::CODE_SKU, $meta, null, 'children');
        $meta = $this->arrayManager->merge(
            $skuPath . static::META_CONFIG_PATH,
            $meta,
            [
                'autoImportIfEmpty' => true
            ]
        );

        $namePath = $this->arrayManager->findPath(ProductAttributeInterface::CODE_NAME, $meta, null, 'children');

        return $this->arrayManager->merge(
            $namePath . static::META_CONFIG_PATH,
            $meta,
            [
                'valueUpdate' => 'keyup'
            ]
        );
    }

    /**
     * The getter function to get the locale currency for real application code
     *
     * @return \Magento\Framework\Locale\CurrencyInterface
     *
     * @deprecated 101.0.0
     */
    private function getLocaleCurrency()
    {
        if ($this->localeCurrency === null) {
            $this->localeCurrency = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Locale\CurrencyInterface::class);
        }
        return $this->localeCurrency;
    }

    /**
     * Format price according to the locale of the currency
     *
     * @param mixed $value
     * @return string
     * @since 101.0.0
     */
    protected function formatPrice($value)
    {
        if (!is_numeric($value)) {
            return null;
        }

        $store = $this->locator->getStore();
        $currency = $this->getLocaleCurrency()->getCurrency($store->getBaseCurrencyCode());
        $value = $currency->toCurrency($value, ['display' => \Magento\Framework\Currency::NO_SYMBOL]);

        return $value;
    }

    /**
     * Format number according to the locale of the currency and precision of input
     *
     * @param mixed $value
     * @return string
     * @since 101.0.0
     */
    protected function formatNumber($value)
    {
        if (!is_numeric($value)) {
            return null;
        }

        $value = (float)$value;
        $precision = strlen(substr(strrchr($value, "."), 1));
        $store = $this->locator->getStore();
        $currency = $this->getLocaleCurrency()->getCurrency($store->getBaseCurrencyCode());
        $value = $currency->toCurrency($value, ['display' => \Magento\Framework\Currency::NO_SYMBOL,
                                                'precision' => $precision]);

        return $value;
    }
}
