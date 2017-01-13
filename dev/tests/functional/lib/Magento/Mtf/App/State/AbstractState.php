<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\App\State;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig;
use Magento\Mtf\ObjectManager;

/**
 * Abstract class AbstractState
 *
 */
abstract class AbstractState implements StateInterface
{
    /**
     * Object Manager.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * List of handlers
     *
     * @var string[]
     */
    private $arguments;

    /**
     * Specifies whether to clean instance under test
     *
     * @var bool
     */
    protected $isCleanInstance = false;

    /**
     * AbstractState constructor.
     *
     * @param ObjectManager $objectManager
     * @param array $arguments
     */
    public function __construct(
        ObjectManager $objectManager,
        array $arguments = []
    ) {
        $this->objectManager = $objectManager;
        $this->arguments = $arguments;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        foreach ($this->arguments as $argument) {
            $handler = $this->objectManager->get($argument);
            $handler->execute($this);
        }
        if ($this->isCleanInstance) {
            $this->clearInstance();
        }
    }

    /**
     * Clear Magento instance: remove all tables in DB and use dump to load new ones, clear Magento cache
     *
     * @throws \Exception
     */
    public function clearInstance()
    {
        $dirList = \Magento\Mtf\ObjectManagerFactory::getObjectManager()
            ->get(\Magento\Framework\Filesystem\DirectoryList::class);

        $configFilePool = \Magento\Mtf\ObjectManagerFactory::getObjectManager()
            ->get(\Magento\Framework\Config\File\ConfigFilePool::class);

        $driverPool = \Magento\Mtf\ObjectManagerFactory::getObjectManager()
            ->get(\Magento\Framework\Filesystem\DriverPool::class);

        $reader = new Reader($dirList, $driverPool, $configFilePool);
        $deploymentConfig = new DeploymentConfig($reader);
        $host = $deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT . '/' . ConfigOptionsListConstants::KEY_HOST
        );
        $user = $deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT . '/' . ConfigOptionsListConstants::KEY_USER
        );
        $password = $deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
            '/' . ConfigOptionsListConstants::KEY_PASSWORD
        );
        $database = $deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT . '/' . ConfigOptionsListConstants::KEY_NAME
        );

        $fileName = MTF_BP . '/' . $database . '.sql';
        if (!file_exists($fileName)) {
            echo('Database dump was not found by path: ' . $fileName);
            return;
        }

        // Drop all tables in database
        $mysqli = new \mysqli($host, $user, $password, $database);
        $mysqli->query('SET foreign_key_checks = 0');
        if ($result = $mysqli->query("SHOW TABLES")) {
            while ($row = $result->fetch_row()) {
                $mysqli->query('DROP TABLE ' . $row[0]);
            }
        }
        $mysqli->query('SET foreign_key_checks = 1');
        $mysqli->close();

        // Load database dump
        exec("mysql -u{$user} -p{$password} {$database} < {$fileName}", $output, $result);
        if ($result) {
            throw new \Exception('Database dump loading has been failed: ' . $output);
        }

        // Clear cache
        exec("rm -rf {$dirList->getPath(DirectoryList::VAR_DIR)}/*", $output, $result);
        if ($result) {
            throw new \Exception('Cleaning Magento cache has been failed: ' . $output);
        }
    }
}
