<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console;

use Magento\Setup\Console\Command\DeployStaticContentCommand;
use Magento\Deploy\Console\Command\DeployStaticOptions as Options;
use Magento\Framework\Validator\Locale;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class InputValidator
 *
 * @api
 */
class InputValidator
{
    /**
     * @var array
     */
    public static $fileExtensionOptionMap = [
        'js' => Options::NO_JAVASCRIPT,
        'map' => Options::NO_JAVASCRIPT,
        'css' => Options::NO_CSS,
        'less' => Options::NO_LESS,
        'html' => Options::NO_HTML,
        'htm' => Options::NO_HTML,
        'jpg' => Options::NO_IMAGES,
        'jpeg' => Options::NO_IMAGES,
        'gif' => Options::NO_IMAGES,
        'png' => Options::NO_IMAGES,
        'ico' => Options::NO_IMAGES,
        'svg' => Options::NO_IMAGES,
        'eot' => Options::NO_FONTS,
        'ttf' => Options::NO_FONTS,
        'woff' => Options::NO_FONTS,
        'woff2' => Options::NO_FONTS,
        'md' => Options::NO_MISC,
        'jbf' => Options::NO_MISC,
        'csv' => Options::NO_MISC,
        'json' => Options::NO_MISC,
        'txt' => Options::NO_MISC,
        'htc' => Options::NO_MISC,
        'swf' => Options::NO_MISC,
        'LICENSE' => Options::NO_MISC,
        '' => Options::NO_MISC,
    ];

    /**
     * Locale interface
     *
     * Used to check if specified locale codes are valid
     *
     * @var Locale
     */
    private $localeValidator;

    /**
     * InputValidator constructor
     *
     * @param Locale $localeValidator
     */
    public function __construct(Locale $localeValidator)
    {
        $this->localeValidator = $localeValidator;
    }

    /**
     * Validate input options
     *
     * @param InputInterface $input
     * @return void
     */
    public function validate(InputInterface $input)
    {
        $this->checkAreasInput(
            $input->getOption(Options::AREA),
            $input->getOption(Options::EXCLUDE_AREA)
        );
        $this->checkThemesInput(
            $input->getOption(Options::THEME),
            $input->getOption(Options::EXCLUDE_THEME)
        );
        $this->checkLanguagesInput(
            $input->getArgument(DeployStaticContentCommand::LANGUAGES_ARGUMENT) ?: ['all'],
            $input->getOption(Options::EXCLUDE_LANGUAGE)
        );
    }

    /**
     * Validate options related to areas
     *
     * @param array $areasInclude
     * @param array $areasExclude
     * @return void
     * @throws \InvalidArgumentException
     */
    private function checkAreasInput(array $areasInclude, array $areasExclude)
    {
        if ($areasInclude[0] != 'all' && $areasExclude[0] != 'none') {
            throw new \InvalidArgumentException(
                '--area (-a) and --exclude-area cannot be used at the same time'
            );
        }
    }

    /**
     * Validate options related to themes
     *
     * @param array $themesInclude
     * @param array $themesExclude
     * @return void
     * @throws \InvalidArgumentException
     */
    private function checkThemesInput(array $themesInclude, array $themesExclude)
    {
        if ($themesInclude[0] != 'all' && $themesExclude[0] != 'none') {
            throw new \InvalidArgumentException(
                '--theme (-t) and --exclude-theme cannot be used at the same time'
            );
        }
    }

    /**
     * Validate options related to locales
     *
     * @param array $languagesInclude
     * @param array $languagesExclude
     * @return void
     * @throws \InvalidArgumentException
     */
    private function checkLanguagesInput(array $languagesInclude, array $languagesExclude)
    {
        if ($languagesInclude[0] != 'all') {
            foreach ($languagesInclude as $lang) {
                if (!$this->localeValidator->isValid($lang)) {
                    throw new \InvalidArgumentException(
                        $lang .
                        ' argument has invalid value, please run info:language:list for list of available locales'
                    );
                }
            }
            if ($languagesExclude[0] != 'none') {
                throw new \InvalidArgumentException(
                    '--language (-l) and --exclude-language cannot be used at the same time'
                );
            }
        }
    }
}
