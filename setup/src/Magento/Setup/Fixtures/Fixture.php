<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
abstract class Fixture
{
    /**
     * @var int
     * @since 2.0.0
     */
    protected $priority;

    /**
     * @var FixtureModel
     * @since 2.0.0
     */
    protected $fixtureModel;

    /**
     * @param FixtureModel $fixtureModel
     * @since 2.0.0
     */
    public function __construct(FixtureModel $fixtureModel)
    {
        $this->fixtureModel = $fixtureModel;
    }

    /**
     * Execute fixture
     *
     * @return void
     * @since 2.0.0
     */
    abstract public function execute();

    /**
     * Get fixture action description
     *
     * @return string
     * @since 2.0.0
     */
    abstract public function getActionTitle();

    /**
     * Print information about generated fixture. Print fixture label and amount of generated items
     *
     * @param OutputInterface $output
     * @return void
     * @since 2.2.0
     */
    public function printInfo(OutputInterface $output)
    {
        foreach ($this->introduceParamLabels() as $configName => $label) {
            $configValue = $this->fixtureModel->getValue($configName);
            $generationCount = is_array($configValue) === true
                ? count($configValue[array_keys($configValue)[0]])
                : $configValue;

            if (!empty($generationCount)) {
                $output->writeln('<info> |- ' . $label . ': ' . $generationCount . '</info>');
            }
        }
    }

    /**
     * Introduce parameters labels
     *
     * @return array
     * @since 2.0.0
     */
    abstract public function introduceParamLabels();

    /**
     * Get fixture priority
     *
     * @return int
     * @since 2.0.0
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
