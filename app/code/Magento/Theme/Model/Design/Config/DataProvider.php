<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Theme\Model\ResourceModel\Design\Config\Collection;
use Magento\Theme\Model\ResourceModel\Design\Config\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Framework\App\RequestInterface;

/**
 * Class \Magento\Theme\Model\Design\Config\DataProvider
 *
 * @since 2.1.0
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     * @since 2.1.0
     */
    protected $loadedData;

    /**
     * @var Collection
     * @since 2.1.0
     */
    protected $collection;

    /**
     * @var DataProvider\DataLoader
     * @since 2.1.0
     */
    protected $dataLoader;

    /**
     * @var DataProvider\MetadataLoader
     * @since 2.1.0
     */
    private $metadataLoader;

    /**
     * @var SettingChecker
     * @since 2.1.3
     */
    private $settingChecker;

    /**
     * @var RequestInterface
     * @since 2.1.3
     */
    private $request;

    /**
     * @var ScopeCodeResolver
     * @since 2.1.3
     */
    private $scopeCodeResolver;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param DataProvider\DataLoader $dataLoader
     * @param DataProvider\MetadataLoader $metadataLoader
     * @param CollectionFactory $configCollectionFactory
     * @param array $meta
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataProvider\DataLoader $dataLoader,
        DataProvider\MetadataLoader $metadataLoader,
        CollectionFactory $configCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->dataLoader = $dataLoader;
        $this->metadataLoader = $metadataLoader;

        $this->collection = $configCollectionFactory->create();

        $this->meta = array_merge($this->meta, $this->metadataLoader->getData());
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = $this->dataLoader->getData();
        return $this->loadedData;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.3
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        if (!isset($meta['other_settings']['children'])) {
            return $meta;
        }

        $request = $this->getRequest()->getParams();
        if (!isset($request['scope'])) {
            return $meta;
        }

        $scope = $request['scope'];
        $scopeCode = $this->getScopeCodeResolver()->resolve(
            $scope,
            isset($request['scope_id']) ? $request['scope_id'] : null
        );

        foreach ($meta['other_settings']['children'] as $settingGroupName => &$settingGroup) {
            foreach ($settingGroup['children'] as $fieldName => &$field) {
                $path = sprintf(
                    'design/%s/%s',
                    $settingGroupName,
                    preg_replace('/^' . $settingGroupName . '_/', '', $fieldName)
                );
                $isReadOnly = $this->getSettingChecker()->isReadOnly(
                    $path,
                    $scope,
                    $scopeCode
                );

                if ($isReadOnly) {
                    $field['arguments']['data']['config']['disabled'] = true;
                    $field['arguments']['data']['config']['is_disable_inheritance'] = true;
                }
            }
        }

        if (isset($meta['other_settings']['children']['search_engine_robots']['children'])) {
            $meta['other_settings']['children']['search_engine_robots']['children'] = array_merge(
                $meta['other_settings']['children']['search_engine_robots']['children'],
                $this->getSearchEngineRobotsMetadata(
                    $scope,
                    $meta['other_settings']['children']['search_engine_robots']['children']
                )
            );
        }

        return $meta;
    }

    /**
     * Retrieve modified Search Engine Robots metadata
     *
     * Disable Search Engine Robots fields in case when current scope is 'stores'.
     *
     * @param string $scope
     * @param array $fields
     * @return array
     * @since 2.2.0
     */
    private function getSearchEngineRobotsMetadata($scope, array $fields = [])
    {
        if ($scope == \Magento\Store\Model\ScopeInterface::SCOPE_STORES) {
            $resetToDefaultsData = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'disabled' => true,
                            'is_disable_inheritance' => true,
                        ],
                    ],
                ],
            ];
            $fields = array_merge($fields, ['reset_to_defaults' => $resetToDefaultsData]);
            foreach ($fields as &$field) {
                $field['arguments']['data']['config']['disabled'] = true;
                $field['arguments']['data']['config']['is_disable_inheritance'] = true;
            }
        }
        return $fields;
    }

    /**
     * @deprecated 2.1.3
     * @return ScopeCodeResolver
     * @since 2.1.3
     */
    private function getScopeCodeResolver()
    {
        if ($this->scopeCodeResolver === null) {
            $this->scopeCodeResolver = ObjectManager::getInstance()->get(ScopeCodeResolver::class);
        }
        return $this->scopeCodeResolver;
    }

    /**
     * @deprecated 2.1.3
     * @return SettingChecker
     * @since 2.1.3
     */
    private function getSettingChecker()
    {
        if ($this->settingChecker === null) {
            $this->settingChecker = ObjectManager::getInstance()->get(SettingChecker::class);
        }
        return $this->settingChecker;
    }

    /**
     * @deprecated 2.1.3
     * @return RequestInterface
     * @since 2.1.3
     */
    private function getRequest()
    {
        if ($this->request === null) {
            $this->request = ObjectManager::getInstance()->get(RequestInterface::class);
        }
        return $this->request;
    }
}
