<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\ContentProcessorException;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\View\Deployment\Version;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Config\Theme;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Translate\Js\Config as JsTranslationConfig;
use Magento\TestFramework\Inspection\Exception;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A service for deploying Magento static view files for production mode
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class Deployer
{
    /** @var Files */
    private $filesUtil;

    /** @var ObjectManagerFactory */
    private $omFactory;

    /** @var OutputInterface */
    private $output;

    /** @var Version\StorageInterface */
    private $versionStorage;

    /** @var \Magento\Framework\View\Asset\Repository */
    private $assetRepo;

    /** @var Publisher */
    private $assetPublisher;

    /** @var \Magento\Framework\View\Asset\Bundle\Manager */
    private $bundleManager;

    /** @var bool */
    private $isDryRun;

    /** @var bool */
    private $isJavaScript;

    /** @var bool */
    private $isCss;

    /** @var bool */
    private $isLess;

    /** @var bool */
    private $isImages;

    /** @var bool */
    private $isFonts;

    /** @var bool */
    private $isHtml;

    /** @var bool */
    private $isMisc;

    /** @var bool */
    private $isHtmlMinify;

    /** @var int */
    private $count;

    /** @var int */
    private $errorCount;

    /** @var \Magento\Framework\View\Template\Html\MinifierInterface */
    private $htmlMinifier;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var JsTranslationConfig
     */
    protected $jsTranslationConfig;

    /**
     * @var AlternativeSourceInterface[]
     */
    private $alternativeSources;

    /** @var array **/
    private $fileExtensionsJs = ['js', 'map'];

    /** @var array **/
    private $fileExtensionsCss = ['css'];

    /** @var array **/
    private $fileExtensionsLess = ['less'];

    /** @var array **/
    private $fileExtensionsHtml = ['html', 'htm'];

    /** @var array **/
    private $fileExtensionsImages = ['jpg', 'jpeg', 'gif', 'png', 'ico', 'svg'];

    /** @var array **/
    private $fileExtensionsFonts = ['eot', 'svg', 'ttf', 'woff', 'woff2'];

    /** @var array **/
    private $fileExtensionsMisc = ['md', 'txt', 'jbf', 'csv', 'json', 'txt', 'htc', 'swf', 'LICENSE', ''];

    /**
     * Constructor
     *
     * @param Files $filesUtil
     * @param OutputInterface $output
     * @param Version\StorageInterface $versionStorage
     * @param JsTranslationConfig $jsTranslationConfig
     * @param AlternativeSourceInterface[] $alternativeSources
     * @param bool $isDryRun
     * @param bool $isJavaScript
     * @param bool $isCss
     * @param bool $isLess
     * @param bool $isImages
     * @param bool $isFonts
     * @param bool $isHtml
     * @param bool $isMisc
     * @param bool $isHtmlMinify
     */
    public function __construct(
        Files $filesUtil,
        OutputInterface $output,
        Version\StorageInterface $versionStorage,
        JsTranslationConfig $jsTranslationConfig,
        array $alternativeSources,
        $isDryRun = false,
        $isJavaScript = false,
        $isCss = false,
        $isLess = false,
        $isImages = false,
        $isFonts = false,
        $isHtml = false,
        $isMisc = false,
        $isHtmlMinify = false
    ) {
        $this->filesUtil = $filesUtil;
        $this->output = $output;
        $this->versionStorage = $versionStorage;
        $this->isDryRun = $isDryRun;
        $this->isJavaScript = $isJavaScript;
        $this->isCss = $isCss;
        $this->isLess = $isLess;
        $this->isImages = $isImages;
        $this->isFonts = $isFonts;
        $this->isHtml = $isHtml;
        $this->isMisc = $isMisc;
        $this->isHtmlMinify = $isHtmlMinify;
        $this->jsTranslationConfig = $jsTranslationConfig;
        $this->parentTheme = [];

        array_map(
            function (AlternativeSourceInterface $alternative) {
            },
            $alternativeSources
        );
        $this->alternativeSources = $alternativeSources;

    }

    /**
     * Check if skip flag is affecting file by extension
     *
     * @param string $filePath
     * @return boolean
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function checkSkip($filePath)
    {
        $path = $filePath;
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $check = ($this->isJavaScript || $this->isCss || $this->isLess || $this->isHtml || $this->isImages || $this->isFonts || $this->isMisc);

        if ($check && $filePath != '.') {
            if ($this->isJavaScript && in_array($ext, $this->fileExtensionsJs)) {
                return true;
            } elseif ($this->isCss && in_array($ext, $this->fileExtensionsCss)) {
                return true;
            } elseif ($this->isLess && in_array($ext, $this->fileExtensionsLess)) {
                return true;
            } elseif ($this->isHtml && in_array($ext, $this->fileExtensionsJs)) {
                return true;
            } elseif ($this->isImages && in_array($ext, $this->fileExtensionsImages)) {
                return true;
            } elseif ($this->isFonts && in_array($ext, $this->fileExtensionsFonts)) {
                return true;
            } elseif ($this->isMisc && in_array($ext, $this->fileExtensionsMisc)) {
                return true;
            }

            return false;
        }
    }

    /**
     * Populate all static view files for specified root path and list of languages
     *
     * @param ObjectManagerFactory $omFactory
     * @param array $areasArg
     * @param array $localesArg
     * @param array $themesArg
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function deploy(ObjectManagerFactory $omFactory, array $areasArg, array $localesArg, array $themesArg)
    {

        $this->omFactory = $omFactory;

        if ($this->isDryRun) {
            $this->output->writeln('Dry run. Nothing will be recorded to the target directory.');
        }

        $areaList = implode(', ', $areasArg);
        $this->output->writeln("Requested areas: {$areaList}");

        $localeList = implode(', ', $localesArg);
        $this->output->writeln("Requested languages: {$localeList}");

        $themeList = implode(', ', $themesArg);
        $this->output->writeln("Requested themes: {$themeList}");
        
        $libFiles = $this->filesUtil->getStaticLibraryFiles();
        list($areas, $appFiles) = $this->collectAppFiles($localesArg);
        foreach ($areasArg as $area) {
            $this->emulateApplicationArea($area);
            foreach ($localesArg as $locale) {
                $this->emulateApplicationLocale($locale, $area);
                foreach ($themesArg as $themePath) {

                    $this->output->writeln("=== {$area} -> {$themePath} -> {$locale} ===");
                    $this->count = 0;
                    $this->errorCount = 0;

                    /** @var \Magento\Theme\Model\View\Design $design */
                    try {
                        $design = $this->objectManager->create('Magento\Theme\Model\View\Design');
                        $design->setDesignTheme($themePath, $area);
                    } catch (\LogicException $e) {
                        continue;
                    }

                    $assetRepo = $this->objectManager->create(
                        'Magento\Framework\View\Asset\Repository',
                        [
                            'design' => $design,
                        ]
                    );
                    /** @var \Magento\RequireJs\Model\FileManager $fileManager */
                    $fileManager = $this->objectManager->create(
                        'Magento\RequireJs\Model\FileManager',
                        [
                            'config' => $this->objectManager->create(
                                'Magento\Framework\RequireJs\Config',
                                [
                                    'assetRepo' => $assetRepo,
                                    'design' => $design,
                                ]
                            ),
                            'assetRepo' => $assetRepo,
                        ]
                    );
                    $fileManager->createRequireJsConfigAsset();

                    foreach ($appFiles as $info) {
                        list($fileArea, $fileTheme, , $module, $filePath) = $info;

                        if ($this->checkSkip($filePath)) {
                            continue;
                        }

                        if (($fileArea == $area || $fileArea == 'base') &&
                            ($fileTheme == '' || $fileTheme == $themePath ||
                                in_array(
                                    $fileArea . Theme::THEME_PATH_SEPARATOR . $fileTheme,
                                    $this->findAncestors($area . Theme::THEME_PATH_SEPARATOR . $themePath)
                                ))
                        ) {
                            $compiledFile = $this->deployFile($filePath, $area, $themePath, $locale, $module);
                            if ($compiledFile !== '') {
                                $this->deployFile($compiledFile, $area, $themePath, $locale, $module);
                            }
                        }
                    }
                    foreach ($libFiles as $filePath) {

                        if ($this->checkSkip($filePath)) {
                            continue;
                        }

                        $compiledFile = $this->deployFile($filePath, $area, $themePath, $locale, null);

                        
                        if ($compiledFile !== '') {
                            $this->deployFile($compiledFile, $area, $themePath, $locale, null);
                        }
                    }
                    if (!$this->isJavaScript) {
                        if ($this->jsTranslationConfig->dictionaryEnabled()) {
                            $this->deployFile(
                                $this->jsTranslationConfig->getDictionaryFileName(),
                                $area,
                                $themePath,
                                $locale,
                                null
                            );
                        }
                        $fileManager->clearBundleJsPool();
                    }
                    $this->bundleManager->flush();
                    $this->output->writeln("\nSuccessful: {$this->count} files; errors: {$this->errorCount}\n---\n");
                }
            }
        }
        if (!$this->isHtmlMinify) {
            $this->output->writeln('=== Minify templates ===');
            $this->count = 0;
            foreach ($this->filesUtil->getPhtmlFiles(false, false) as $template) {
                $this->htmlMinifier->minify($template);
                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->output->writeln($template . " minified\n");
                } else {
                    $this->output->write('.');
                }
                $this->count++;
            }
            $this->output->writeln("\nSuccessful: {$this->count} files modified\n---\n");
            $version = (new \DateTime())->getTimestamp();
            $this->output->writeln("New version of deployed files: {$version}");
            if (!$this->isDryRun) {
                $this->versionStorage->save($version);
            }
        }
    }

    /**
     * Accumulate all static view files in the application and record all found areas, themes and languages
     *
     * Returns an array of areas and files with meta information
     *
     * @param array $requestedLocales
     * @return array
     */
    private function collectAppFiles($requestedLocales)
    {
        $areas = [];
        $locales = [];
        $files = $this->filesUtil->getStaticPreProcessingFiles();
        foreach ($files as $info) {
            list($area, $themePath, $locale) = $info;
            if ($themePath) {
                $areas[$area][$themePath] = $themePath;
            }
            if ($locale) {
                $locales[$locale] = $locale;
            }
        }
        foreach ($requestedLocales as $locale) {
            unset($locales[$locale]);
        }
        if (!empty($locales)) {
            $langList = implode(', ', $locales);
            $this->output->writeln(
                "WARNING: there were files for the following languages detected in the file system: {$langList}."
                . ' These languages were not requested, so the files will not be populated.'
            );
        }

        return [$areas, $files];
    }

    /**
     * Emulate application area and various services that are necessary for populating files
     *
     * @param string $areaCode
     * @return void
     */
    private function emulateApplicationArea($areaCode)
    {
        $this->objectManager = $this->omFactory->create(
            [\Magento\Framework\App\State::PARAM_MODE => \Magento\Framework\App\State::MODE_DEFAULT]
        );
        /** @var \Magento\Framework\App\State $appState */
        $appState = $this->objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode($areaCode);
        $this->assetRepo = $this->objectManager->get('Magento\Framework\View\Asset\Repository');
        $this->assetPublisher = $this->objectManager->create('Magento\Framework\App\View\Asset\Publisher');
        $this->htmlMinifier = $this->objectManager->get('Magento\Framework\View\Template\Html\MinifierInterface');
        $this->bundleManager = $this->objectManager->get('Magento\Framework\View\Asset\Bundle\Manager');

    }

    /**
     * Set application locale and load translation for area
     *
     * @param string $locale
     * @param string $area
     * @return void
     */
    protected function emulateApplicationLocale($locale, $area)
    {
        /** @var \Magento\Framework\TranslateInterface $translator */
        $translator = $this->objectManager->get('Magento\Framework\TranslateInterface');
        $translator->setLocale($locale);
        $translator->loadData($area, true);
        /** @var \Magento\Framework\Locale\ResolverInterface $localeResolver */
        $localeResolver = $this->objectManager->get('Magento\Framework\Locale\ResolverInterface');
        $localeResolver->setLocale($locale);
    }

    /**
     * Deploy a static view file
     *
     * @param string $filePath
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @return string
     * @throws \InvalidArgumentException
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function deployFile($filePath, $area, $themePath, $locale, $module)
    {
        $compiledFile = '';
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        foreach ($this->alternativeSources as $name => $alternative) {
            if (in_array($extension, $alternative->getAlternativesExtensionsNames(), true)
                && strpos(basename($filePath), '_') !== 0
            ) {
                $compiledFile = substr($filePath, 0, strlen($filePath) - strlen($extension) - 1);
                $compiledFile = $compiledFile . '.' . $name;
            }
        }

        if ($this->output->isVeryVerbose()) {
            $logMessage = "Processing file '$filePath' for area '$area', theme '$themePath', locale '$locale'";
            if ($module) {
                $logMessage .= ", module '$module'";
            }
            $this->output->writeln($logMessage);
        }

        try {
            $asset = $this->assetRepo->createAsset(
                $filePath,
                ['area' => $area, 'theme' => $themePath, 'locale' => $locale, 'module' => $module]
            );
            if ($this->output->isVeryVerbose()) {
                $this->output->writeln("\tDeploying the file to '{$asset->getPath()}'");
            } else {
                $this->output->write('.');
            }
            if ($this->isDryRun) {
                $asset->getContent();
            } else {
                $this->assetPublisher->publish($asset);
                $this->bundleManager->addAsset($asset);
            }
            $this->count++;
        } catch (ContentProcessorException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->output->write('.');
            $this->verboseLog($exception->getTraceAsString());
            $this->errorCount++;
        }

        return $compiledFile;
    }

    /**
     * Find ancestor themes' full paths
     *
     * @param string $themeFullPath
     * @return string[]
     */
    private function findAncestors($themeFullPath)
    {
        /** @var \Magento\Framework\View\Design\Theme\ListInterface $themeCollection */
        $themeCollection = $this->objectManager->get('Magento\Framework\View\Design\Theme\ListInterface');
        $theme = $themeCollection->getThemeByFullPath($themeFullPath);
        $ancestors = $theme->getInheritedThemes();
        $ancestorThemeFullPath = [];
        foreach ($ancestors as $ancestor) {
            $ancestorThemeFullPath[] = $ancestor->getFullPath();
        }
        return $ancestorThemeFullPath;
    }

    /**
     * Verbose log
     *
     * @param string $message
     * @return void
     */
    private function verboseLog($message)
    {
        if ($this->output->isVerbose()) {
            $this->output->writeln($message);
        }
    }
}
