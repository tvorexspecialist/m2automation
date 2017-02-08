<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Console\GenerationDirectoryAccess;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Phrase;
use Magento\Setup\Console\Command\DiCompileCommand;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Symfony\Component\Console\Input\ArgvInput;
use Zend\ServiceManager\ServiceManager;

/**
 * Class prepares folders for code generation
 */
class CompilerPreparation
{
    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @var ArgvInput
     */
    private $input;

    /**
     * @var File
     */
    private $filesystemDriver;

    /**
     * @var GenerationDirectoryAccess
     */
    private $generationDirectoryAccess;

    /**
     * @param ServiceManager $serviceManager
     * @param ArgvInput $input
     * @param File $filesystemDriver
     */
    public function __construct(
        ServiceManager $serviceManager,
        ArgvInput $input,
        File $filesystemDriver
    ) {
        $this->serviceManager = $serviceManager;
        $this->input = $input;
        $this->filesystemDriver = $filesystemDriver;
    }

    /**
     * Determine whether a CLI command is for compilation, and if so, clear the directory.
     *
     * @throws FileSystemException if generation directory is read-only
     * @return void
     */
    public function handleCompilerEnvironment()
    {
        $compilationCommands = [DiCompileCommand::NAME];
        $cmdName = $this->input->getFirstArgument();
        $isHelpOption = $this->input->hasParameterOption('--help') || $this->input->hasParameterOption('-h');
        if (!in_array($cmdName, $compilationCommands) || $isHelpOption) {
            return;
        }
        $compileDirList = [];
        $mageInitParams = $this->serviceManager->get(InitParamListener::BOOTSTRAP_PARAM);
        $mageDirs = isset($mageInitParams[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS])
            ? $mageInitParams[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS]
            : [];
        $directoryList = new DirectoryList(BP, $mageDirs);
        $compileDirList[] = $directoryList->getPath(DirectoryList::GENERATED_CODE);
        $compileDirList[] = $directoryList->getPath(DirectoryList::GENERATED_METADATA);

        if (!$this->getGenerationDirectoryAccess()->check()) {
            throw new FileSystemException(
                new Phrase('Generation directory can not be written.')
            );
        }

        foreach ($compileDirList as $compileDir) {
            if ($this->filesystemDriver->isExists($compileDir)) {
                $this->filesystemDriver->deleteDirectory($compileDir);
            }
        }
    }

    /**
     * Retrieves generation directory access checker.
     *
     * @return GenerationDirectoryAccess the generation directory access checker
     */
    private function getGenerationDirectoryAccess()
    {
        if (null === $this->generationDirectoryAccess) {
            $this->generationDirectoryAccess = new GenerationDirectoryAccess($this->serviceManager);
        }

        return $this->generationDirectoryAccess;
    }
}
