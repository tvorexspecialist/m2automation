<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;

/**
 * Base test case for message queue tests.
 */
abstract class QueueTestCaseAbstract extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string[]
     */
    protected $consumers = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @var string
     */
    protected $logFilePath;

    /**
     * @var int|null
     */
    protected $maxMessages = null;

    /**
     * @var PublisherConsumerController
     */
    private $publisherConsumerController;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->logFilePath = TESTS_TEMP_DIR . "/MessageQueueTestLog.txt";
        $this->publisherConsumerController = $this->objectManager->create(PublisherConsumerController::class, [
            'consumers' => $this->consumers,
            'logFilePath' => $this->logFilePath,
            'maxMessages' => $this->maxMessages,
            'appInitParams' => \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams()
        ]);

        try {
            $this->publisherConsumerController->initialize();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail(
                $e->getMessage()
            );
        }
        $this->publisher = $this->publisherConsumerController->getPublisher();
    }

    protected function tearDown()
    {
        $this->publisherConsumerController->stopConsumers();
    }

    /**
     * Wait for asynchronous handlers to log data to file.
     *
     * @param int $expectedLinesCount
     * @param string $logFilePath
     */
    protected function waitForAsynchronousResult($expectedLinesCount, $logFilePath)
    {
        try {
            $this->publisherConsumerController->waitForAsynchronousResult($expectedLinesCount, $logFilePath);
        } catch (PreconditionFailedException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Workaround for https://bugs.php.net/bug.php?id=72286
     */
    public static function tearDownAfterClass()
    {
        if (version_compare(phpversion(), '7') == -1) {
            $closeConnection = new \ReflectionMethod(\Magento\Amqp\Model\Config::class, 'closeConnection');
            $closeConnection->setAccessible(true);

            $config = Bootstrap::getObjectManager()->get(\Magento\Amqp\Model\Config::class);
            $closeConnection->invoke($config);
        }
    }
}
