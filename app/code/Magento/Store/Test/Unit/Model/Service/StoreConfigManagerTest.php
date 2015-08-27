<?php
/**
 * Test class for \Magento\Store\Model\Store\Service\StoreConfigManager
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model\Service;

use Magento\Store\Model\ScopeInterface;

class StoreConfigManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Service\StoreConfigManager
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Resource\Store\CollectionFactory
     */
    protected $storeCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Data\StoreConfigFactory
     */
    protected $storeConfigFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->storeConfigFactoryMock = $this->getMockBuilder('\Magento\Store\Model\Data\StoreConfigFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->storeCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Store\Model\Resource\Store\CollectionFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');

        $this->model = new \Magento\Store\Model\Service\StoreConfigManager(
            $this->storeCollectionFactoryMock,
            $this->scopeConfigMock,
            $this->storeConfigFactoryMock
        );
    }

    protected function getStoreMock(array $storeConfig)
    {
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeConfig['id']);
        $storeMock->expects($this->any())
            ->method('getCode')
            ->willReturn($storeConfig['code']);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($storeConfig['website_id']);

        $urlMap = [
            [\Magento\Framework\UrlInterface::URL_TYPE_WEB, false, $storeConfig['base_url']],
            [\Magento\Framework\UrlInterface::URL_TYPE_WEB, true, $storeConfig['secure_base_url']],
            [\Magento\Framework\UrlInterface::URL_TYPE_LINK, false, $storeConfig['base_link_url']],
            [\Magento\Framework\UrlInterface::URL_TYPE_LINK, true, $storeConfig['secure_base_link_url']],
            [\Magento\Framework\UrlInterface::URL_TYPE_STATIC, false, $storeConfig['base_static_url']],
            [\Magento\Framework\UrlInterface::URL_TYPE_STATIC, true, $storeConfig['secure_base_static_url']],
            [\Magento\Framework\UrlInterface::URL_TYPE_MEDIA, false, $storeConfig['base_media_url']],
            [\Magento\Framework\UrlInterface::URL_TYPE_MEDIA, true, $storeConfig['secure_base_media_url']],
        ];
        $storeMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturnMap($urlMap);

        return $storeMock;
    }

    protected function createStoreConfigDataObject()
    {
        /** @var \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactoryMock */
        $extensionFactoryMock = $this->getMockBuilder('\Magento\Framework\Api\ExtensionAttributesFactory')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Framework\Api\AttributeValueFactory $attributeValueFactoryMock */
        $attributeValueFactoryMock = $this->getMockBuilder('\Magento\Framework\Api\AttributeValueFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $storeConfigDataObject = new \Magento\Store\Model\Data\StoreConfig(
            $extensionFactoryMock,
            $attributeValueFactoryMock,
            []
        );
        return $storeConfigDataObject;
    }

    public function testGetStoreConfigs()
    {
        $id = 1;
        $code = 'default';
        $websiteId = 1;
        $baseUrl = 'http://magento/base_url';
        $secureBaseUrl = 'https://magento/base_url';
        $baseLinkUrl = 'http://magento/base_url/links';
        $secureBaseLinkUrl = 'https://magento/base_url/links';
        $baseStaticUrl = 'http://magento/base_url/pub/static';
        $secureBaseStaticUrl = 'https://magento/base_url/static';
        $baseMediaUrl = 'http://magento/base_url/pub/media';
        $secureBaseMediaUrl = 'https://magento/base_url/pub/media';
        $locale = 'en_US';
        $timeZone = 'America/Los_Angeles';
        $baseCurrencyCode = 'USD';
        $defaultDisplayCurrencyCode = 'GBP';

        $storeMocks = [];
        $storeConfigs = [
            'id' => $id,
            'code' => $code,
            'website_id' => $websiteId,
            'base_url' => $baseUrl,
            'secure_base_url' => $secureBaseUrl,
            'base_link_url' => $baseLinkUrl,
            'secure_base_link_url' => $secureBaseLinkUrl,
            'base_static_url' => $baseStaticUrl,
            'secure_base_static_url' => $secureBaseStaticUrl,
            'base_media_url' => $baseMediaUrl,
            'secure_base_media_url' => $secureBaseMediaUrl,
        ];
        $storeMocks[] = $this->getStoreMock($storeConfigs);

        $storeCollectionMock = $this->getMockBuilder('\Magento\Store\Model\Resource\Store\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $storeCollectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('code', ['in' => [$code]])
            ->willReturnSelf();
        $storeCollectionMock->expects($this->once())
            ->method('load')
            ->willReturn($storeMocks);
        $this->storeCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($storeCollectionMock);

        $storeConfigDataObject = $this->createStoreConfigDataObject();
        $this->storeConfigFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($storeConfigDataObject);
        $configValues = [
            ['general/locale/code', ScopeInterface::SCOPE_STORES, $code, $locale],
            ['currency/options/base', ScopeInterface::SCOPE_STORES, $code, $baseCurrencyCode],
            ['currency/options/default', ScopeInterface::SCOPE_STORES, $code, $defaultDisplayCurrencyCode],
            ['general/locale/timezone', ScopeInterface::SCOPE_STORES, $code, $timeZone],
        ];
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap($configValues);

        $result = $this->model->getStoreConfigs([$code]);

        $this->assertEquals(1, count($result));
        $this->assertEquals($id, $result[0]->getId());
        $this->assertEquals($code, $result[0]->getCode());
        $this->assertEquals('lbs', $result[0]->getWeightUnit());
        $this->assertEquals($baseUrl, $result[0]->getBaseUrl());
        $this->assertEquals($secureBaseUrl, $result[0]->getSecureBaseUrl());
        $this->assertEquals($baseLinkUrl, $result[0]->getBaseLinkUrl());
        $this->assertEquals($secureBaseLinkUrl, $result[0]->getSecureBaseLinkUrl());
        $this->assertEquals($baseStaticUrl, $result[0]->getBaseStaticUrl());
        $this->assertEquals($secureBaseStaticUrl, $result[0]->getSecureBaseStaticUrl());
        $this->assertEquals($baseMediaUrl, $result[0]->getBaseMediaUrl());
        $this->assertEquals($secureBaseMediaUrl, $result[0]->getSecureBaseMediaUrl());

        $this->assertEquals($timeZone, $result[0]->getTimezone());
        $this->assertEquals($locale, $result[0]->getLocale());
        $this->assertEquals($baseCurrencyCode, $result[0]->getBaseCurrencyCode());
        $this->assertEquals($defaultDisplayCurrencyCode, $result[0]->getDefaultDisplayCurrencyCode());
    }
}
