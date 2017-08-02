<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SampleData\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\SampleData\Model\Dependency;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\ArrayInputFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Composer\Console\Application;
use Composer\Console\ApplicationFactory;

/**
 * Command for remove Sample Data packages
 * @since 2.0.0
 */
class SampleDataRemoveCommand extends Command
{
    /**
     * @var Filesystem
     * @since 2.0.0
     */
    private $filesystem;

    /**
     * @var Dependency
     * @since 2.0.0
     */
    private $sampleDataDependency;

    /**
     * @var ArrayInputFactory
     * @deprecated 2.1.0
     * @since 2.0.0
     */
    private $arrayInputFactory;

    /**
     * @var ApplicationFactory
     * @since 2.0.0
     */
    private $applicationFactory;

    /**
     * @param Filesystem $filesystem
     * @param Dependency $sampleDataDependency
     * @param ArrayInputFactory $arrayInputFactory
     * @param ApplicationFactory $applicationFactory
     * @since 2.0.0
     */
    public function __construct(
        Filesystem $filesystem,
        Dependency $sampleDataDependency,
        ArrayInputFactory $arrayInputFactory,
        ApplicationFactory $applicationFactory
    ) {
        $this->filesystem = $filesystem;
        $this->sampleDataDependency = $sampleDataDependency;
        $this->arrayInputFactory = $arrayInputFactory;
        $this->applicationFactory = $applicationFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('sampledata:remove')
            ->setDescription('Remove all sample data packages from composer.json');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sampleDataPackages = $this->sampleDataDependency->getSampleDataPackages();
        if (!empty($sampleDataPackages)) {
            $baseDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
            $commonArgs = ['--working-dir' => $baseDir, '--no-interaction' => 1, '--no-progress' => 1];
            $packages = array_keys($sampleDataPackages);
            $arguments = array_merge(['command' => 'remove', 'packages' => $packages], $commonArgs);
            $commandInput = new ArrayInput($arguments);

            /** @var Application $application */
            $application = $this->applicationFactory->create();
            $application->setAutoExit(false);
            $result = $application->run($commandInput, $output);
            if ($result !== 0) {
                $output->writeln('<info>' . 'There is an error during remove sample data.' . '</info>');
            }
        } else {
            $output->writeln('<info>' . 'There is no sample data for current set of modules.' . '</info>');
        }
    }
}
