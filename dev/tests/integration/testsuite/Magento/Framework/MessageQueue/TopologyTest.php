<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * @see dev/tests/integration/_files/Magento/TestModuleMessageQueueConfiguration
 * @see dev/tests/integration/_files/Magento/TestModuleMessageQueueConfigOverride
 */
class TopologyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * List of declared exchanges.
     *
     * @var array
     */
    private $declaredExchanges;

    /**
     * @var \Magento\TestFramework\Helper\Amqp
     */
    private $helper;

    protected function setUp()
    {
        $this->helper = new \Magento\TestFramework\Helper\Amqp();
        $this->declaredExchanges = $this->helper->getExchanges();
    }

    /**
     * @dataProvider exchangeDataProvider
     * @param array $expectedConfig
     * @param array $bindingConfig
     */
    public function testTopologyInstallation(array $expectedConfig, array $bindingConfig)
    {
        $name = $expectedConfig['name'];
        $this->assertArrayHasKey($name, $this->declaredExchanges);
        unset($this->declaredExchanges[$name]['message_stats']);
        $this->assertEquals(
            $expectedConfig,
            $this->declaredExchanges[$name],
            'Invalid exchange configuration: ' . $name
        );

        $bindings = $this->helper->getExchangeBindings($name);
        $bindings = array_map(function ($value) {
            unset($value['properties_key']);
            return $value;
        }, $bindings);
        $this->assertEquals(
            $bindingConfig,
            $bindings,
            'Invalid exchange bindings configuration: ' . $name
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function exchangeDataProvider()
    {
        return [
            'magento-topic-based-exchange1' => [
                'exchangeConfig' => [
                    'name' => 'magento-topic-based-exchange1',
                    'vhost' => '/',
                    'type' => 'topic',
                    'durable' => true,
                    'auto_delete' => false,
                    'internal' => false,
                    'arguments' => [
                        'alternate-exchange' => 'magento-log-exchange'
                    ],
                ],
                'bindingConfig' => [
                    [
                        'source' => 'magento-topic-based-exchange1',
                        'vhost' => '/',
                        'destination' => 'topic-queue1',
                        'destination_type' => 'queue',
                        'routing_key' => 'anotherTopic1',
                        'arguments' => [
                            'argument1' => 'value'
                        ],
                    ],
                ]
            ],
            'magento-topic-based-exchange2' => [
                'exchangeConfig' => [
                    'name' => 'magento-topic-based-exchange2',
                    'vhost' => '/',
                    'type' => 'topic',
                    'durable' => true,
                    'auto_delete' => false,
                    'internal' => false,
                    'arguments' => [
                        'alternate-exchange' => 'magento-log-exchange',
                        'arrayValue' => ['10', '20']
                    ],
                ],
                'bindingConfig' => [
                    [
                        'source' => 'magento-topic-based-exchange2',
                        'vhost' => '/',
                        'destination' => 'topic-queue2',
                        'destination_type' => 'queue',
                        'routing_key' => 'anotherTopic2',
                        'arguments' => [
                            'argument1' => 'value',
                            'argument2' => true,
                            'argument3' => '150',
                        ],
                    ],
                ]
            ],
            'magento-topic-based-exchange3' => [
                'exchangeConfig' => [
                    'name' => 'magento-topic-based-exchange3',
                    'vhost' => '/',
                    'type' => 'topic',
                    'durable' => false,
                    'auto_delete' => true,
                    'internal' => true,
                    'arguments' => [],
                ],
                'bindingConfig' => [],
            ],
            'magento-topic-based-exchange4' => [
                'exchangeConfig' => [
                    'name' => 'magento-topic-based-exchange4',
                    'vhost' => '/',
                    'type' => 'topic',
                    'durable' => true,
                    'auto_delete' => false,
                    'internal' => false,
                    'arguments' => [],
                ],
                'bindingConfig' => [
                    [
                        'source' => 'magento-topic-based-exchange4',
                        'vhost' => '/',
                        'destination' => 'topic-queue1',
                        'destination_type' => 'queue',
                        'routing_key' => '#',
                        'arguments' => [
                            'test' => 'one'
                        ],
                    ],
                    [
                        'source' => 'magento-topic-based-exchange4',
                        'vhost' => '/',
                        'destination' => 'topic-queue2',
                        'destination_type' => 'queue',
                        'routing_key' => '*.*.*',
                        'arguments' => [],
                    ],
                ]
            ],
        ];
    }
}
