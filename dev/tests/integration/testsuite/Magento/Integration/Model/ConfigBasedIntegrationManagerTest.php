<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

/**
 * Test class for \Magento\Integration\Model\ConfigBasedIntegrationManager.php.
 */
class ConfigBasedIntegrationManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $consolidatedConfigMock;

    /**
     * @var \Magento\Integration\Model\ConfigBasedIntegrationManager
     */
    protected $integrationManager;

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    protected $integrationService;

    protected function setUp()
    {
        parent::setUp();
        /**
         * @var $objectManager \Magento\TestFramework\ObjectManager
         */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->consolidatedConfigMock = $this->createMock(\Magento\Integration\Model\ConsolidatedConfig::class);
        $objectManager->addSharedInstance(
            $this->consolidatedConfigMock,
            \Magento\Integration\Model\ConsolidatedConfig::class
        );
        $this->integrationManager = $objectManager->create(
            \Magento\Integration\Model\ConfigBasedIntegrationManager::class,
            []
        );
        $this->integrationService = $objectManager->create(
            \Magento\Integration\Api\IntegrationServiceInterface::class,
            []
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testProcessConfigBasedIntegrations()
    {
        $newIntegrations = require __DIR__ . '/Config/Consolidated/_files/integration.php';
        $this->consolidatedConfigMock
            ->expects($this->any())
            ->method('getIntegrations')
            ->willReturn($newIntegrations);

        // Check that the integrations do not exist already
        foreach ($newIntegrations as $integrationName => $integrationData) {
            $integration = $this->integrationService->findByName($integrationName);
            $this->assertEquals(null, $integration->getId(), 'Integration already exists');
        }

        // Create new integrations
        $this->assertEquals(
            $newIntegrations,
            $this->integrationManager->processConfigBasedIntegrations($newIntegrations),
            'Error processing config based integrations.'
        );
        $createdIntegrations = [];

        // Check that the integrations are new with "inactive" status
        foreach ($newIntegrations as $integrationName => $integrationData) {
            $integration = $this->integrationService->findByName($integrationName);
            $this->assertNotEmpty($integration->getId(), 'Integration was not created');
            $this->assertEquals(
                $integration::STATUS_INACTIVE,
                $integration->getStatus(),
                'Integration is not created with "inactive" status'
            );
            $createdIntegrations[$integrationName] = $integration;
        }

        // Rerun integration creation with the same data (data has not changed)
        $this->assertEquals(
            $newIntegrations,
            $this->integrationManager->processConfigBasedIntegrations($newIntegrations),
            'Error processing config based integrations.'
        );

        // Check that the integrations are not recreated when data has not actually changed
        foreach ($newIntegrations as $integrationName => $integrationData) {
            $integration = $this->integrationService->findByName($integrationName);
            $this->assertEquals(
                $createdIntegrations[$integrationName]->getId(),
                $integration->getId(),
                'Integration ID has changed'
            );
            $this->assertEquals(
                $createdIntegrations[$integrationName]->getStatus(),
                $integration->getStatus(),
                'Integration status has changed'
            );
        }
    }
}
