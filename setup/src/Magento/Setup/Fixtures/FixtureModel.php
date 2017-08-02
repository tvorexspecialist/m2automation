<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento model for performance tests
 */
namespace Magento\Setup\Fixtures;

use Magento\Indexer\Console\Command\IndexerReindexCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class FixtureModel
{
    /**
     * Area code
     */
    const AREA_CODE = 'adminhtml';

    /**
     * Fixtures file name pattern
     */
    const FIXTURE_PATTERN = '?*Fixture.php';

    /**
     * Application object
     *
     * @var \Magento\Framework\AppInterface
     * @since 2.0.0
     */
    protected $application;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * List of fixtures applied to the application
     *
     * @var \Magento\Setup\Fixtures\Fixture[]
     * @since 2.0.0
     */
    protected $fixtures = [];

    /**
     * Parameters labels
     *
     * @var array
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $paramLabels = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $initArguments;

    /**
     * @var FixtureConfig
     * @since 2.0.0
     */
    private $config;

    /**
     * Constructor
     *
     * @param IndexerReindexCommand $reindexCommand
     * @param array $initArguments
     * @since 2.0.0
     */
    public function __construct(IndexerReindexCommand $reindexCommand, $initArguments = [])
    {
        $this->initArguments = $initArguments;
        $this->reindexCommand = $reindexCommand;
    }

    /**
     * Run reindex
     *
     * @param OutputInterface $output
     * @return void
     * @since 2.0.0
     */
    public function reindex(OutputInterface $output)
    {
        $input = new ArrayInput([]);
        $this->reindexCommand->run($input, $output);
    }

    /**
     * Load fixtures
     *
     * @return $this
     * @throws \Exception
     * @since 2.0.0
     */
    public function loadFixtures()
    {
        $files = glob(__DIR__ . DIRECTORY_SEPARATOR . self::FIXTURE_PATTERN);

        foreach ($files as $file) {
            $file = basename($file, '.php');
            /** @var \Magento\Setup\Fixtures\Fixture $fixture */
            $type = 'Magento\Setup\Fixtures' . '\\' . $file;
            $fixture = $this->getObjectManager()->create(
                $type,
                [
                    'fixtureModel' => $this,
                ]
            );
            if (isset($this->fixtures[$fixture->getPriority()])) {
                throw new \InvalidArgumentException(
                    sprintf('Duplicate priority %d in fixture %s', $fixture->getPriority(), $type)
                );
            }
            $this->fixtures[$fixture->getPriority()] = $fixture;
        }

        ksort($this->fixtures);
        return $this;
    }

    /**
     * Get param labels
     *
     * @return array
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    public function getParamLabels()
    {
        return $this->paramLabels;
    }

    /**
     * Get fixtures
     *
     * @return Fixture[]
     * @since 2.0.0
     */
    public function getFixtures()
    {
        return $this->fixtures;
    }

    /**
     * Get object manager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    public function getObjectManager()
    {
        if (!$this->objectManager) {
            $objectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(
                BP,
                $this->initArguments
            );
            $this->objectManager = $objectManagerFactory->create($this->initArguments);
            $this->objectManager->get(\Magento\Framework\App\State::class)->setAreaCode(self::AREA_CODE);
        }

        return $this->objectManager;
    }

    /**
     *  Init Object Manager
     *
     * @param string $area
     * @return FixtureModel
     * @since 2.0.0
     */
    public function initObjectManager($area = self::AREA_CODE)
    {
        $objectManger = $this->getObjectManager();
        $configuration = $objectManger
            ->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class)
            ->load($area);
        $objectManger->configure($configuration);

        $diConfiguration = $this->getValue('di');
        if (file_exists($diConfiguration)) {
            $dom = new \DOMDocument();
            $dom->load($diConfiguration);

            $objectManger->configure(
                $objectManger
                    ->get(\Magento\Framework\ObjectManager\Config\Mapper\Dom::class)
                    ->convert($dom)
            );
        }

        $objectManger->get(\Magento\Framework\Config\ScopeInterface::class)
            ->setCurrentScope($area);
        return $this;
    }

    /**
     * Reset object manager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    public function resetObjectManager()
    {
        return $this;
    }

    /**
     * @return FixtureConfig
     * @since 2.2.0
     */
    private function getConfig()
    {
        if (null === $this->config) {
            $this->config = $this->getObjectManager()->get(FixtureConfig::class);
        }

        return $this->config;
    }

    /**
     * Load config from file
     *
     * @param string $filename
     * @throws \Exception
     *
     * @return void
     * @since 2.0.0
     */
    public function loadConfig($filename)
    {
        return $this->getConfig()->loadConfig($filename);
    }

    /**
     * Get profile configuration value
     *
     * @param string $key
     * @param null|mixed $default
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getValue($key, $default = null)
    {
        return $this->getConfig()->getValue($key, $default);
    }
}
