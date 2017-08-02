<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Console\Exception\GenerationDirectoryAccessException;
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
 * @since 2.0.0
 */
class CompilerPreparation
{
    /**
     * @var ServiceManager
     * @since 2.0.0
     */
    private $serviceManager;

    /**
     * @var ArgvInput
     * @since 2.0.0
     */
    private $input;

    /**
     * @var File
     * @since 2.0.0
     */
    private $filesystemDriver;

    /**
     * @var GenerationDirectoryAccess
     * @since 2.2.0
     */
    private $generationDirectoryAccess;

    /**
     * @param ServiceManager $serviceManager
     * @param ArgvInput $input
     * @param File $filesystemDriver
     * @since 2.0.0
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
     * @throws GenerationDirectoryAccessException If generation directory is read-only
     * @return void
     * @since 2.0.0
     */
    public function handleCompilerEnvironment()
    {
        $compilationCommands = $this->getCompilerInvalidationCommands();
        $cmdName = $this->input->getFirstArgument();
        $isHelpOption = $this->input->hasParameterOption('--help') || $this->input->hasParameterOption('-h');
        if (!in_array($cmdName, $compilationCommands) || $isHelpOption) {
            return;
        }

        if (!$this->getGenerationDirectoryAccess()->check()) {
            throw new GenerationDirectoryAccessException();
        }

        $mageInitParams = $this->serviceManager->get(InitParamListener::BOOTSTRAP_PARAM);
        $mageDirs = isset($mageInitParams[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS])
            ? $mageInitParams[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS]
            : [];
        $directoryList = new DirectoryList(BP, $mageDirs);
        $compileDirList = [
            $directoryList->getPath(DirectoryList::GENERATED_CODE),
            $directoryList->getPath(DirectoryList::GENERATED_METADATA),
        ];

        foreach ($compileDirList as $compileDir) {
            if ($this->filesystemDriver->isExists($compileDir)) {
                $this->filesystemDriver->deleteDirectory($compileDir);
            }
        }
    }

    /**
     * Retrieves command list with commands which invalidates compiler
     *
     * @return array
     * @since 2.2.0
     */
    private function getCompilerInvalidationCommands()
    {
        return [
            DiCompileCommand::NAME,
            'module:disable',
            'module:enable',
            'module:uninstall',
            'deploy:mode:set'
        ];
    }

    /**
     * Retrieves generation directory access checker.
     *
     * @return GenerationDirectoryAccess the generation directory access checker
     * @since 2.2.0
     */
    private function getGenerationDirectoryAccess()
    {
        if (null === $this->generationDirectoryAccess) {
            $this->generationDirectoryAccess = new GenerationDirectoryAccess($this->serviceManager);
        }

        return $this->generationDirectoryAccess;
    }
}
