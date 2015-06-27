<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Setup;

use Magento\Backend\Setup\ConfigOptionsList;
use Magento\Framework\Config\File\ConfigFilePool;

class ConfigOptionsListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigOptionsList
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    protected function setUp()
    {
        $this->object = new ConfigOptionsList();
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
    }

    public function testGetOptions()
    {
        $options = $this->object->getOptions();
        $this->assertInternalType('array', $options);
        foreach ($options as $option) {
            $this->assertInstanceOf('\Magento\Framework\Setup\Option\AbstractConfigOption', $option);
        }
    }

    public function testCreateConfig()
    {
        $options = [ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'admin'];
        $actualConfig = $this->object->createConfig($options, $this->deploymentConfig);

        $expectedData = [
            [
                'file' => ConfigFilePool::APP_ENV,
                'segment' => 'backend',
                'data' => [
                    'backend' => ['frontName' => 'admin']
                ]
            ]
        ];

        $this->assertInternalType('array', $actualConfig);
        /** @var \Magento\Framework\Config\Data\ConfigData $config */
        foreach ($actualConfig as $i => $config) {
            $this->assertInstanceOf('\Magento\Framework\Config\Data\ConfigData', $config);
            $this->assertSame($expectedData[$i]['file'], $config->getFileKey());
            $this->assertSame($expectedData[$i]['data'], $config->getData());
        }
    }

    public function testValidate()
    {
        $options = [ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'admin'];
        $errors = $this->object->validate($options, $this->deploymentConfig);
        $this->assertEmpty($errors);
    }

    /**
     * @param array $options
     * @param string $expectedError
     * @dataProvider validateInvalidDataProvider
     */
    public function testValidateInvalid(array $options, $expectedError)
    {
        $errors = $this->object->validate($options, $this->deploymentConfig);
        $this->assertSame([$expectedError], $errors);
    }

    /**
     * @return array
     */
    public function validateInvalidDataProvider()
    {
        return [
            [[ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => '**'], "Invalid backend frontname '**'"],
            [
                [ConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME => 'invalid frontname'],
                "Invalid backend frontname 'invalid frontname'"
            ],
        ];
    }
}
