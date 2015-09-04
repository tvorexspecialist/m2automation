<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\Fallback;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Design\Fallback\Rule\Composite;
use Magento\Framework\View\Design\Fallback\Rule\ModularSwitch;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use Magento\Framework\View\Design\Fallback\Rule\Simple;
use Magento\Framework\View\Design\Fallback\Rule\Theme;

/**
 * Fallback Factory
 *
 * Factory that produces all sorts of fallback rules
 */
class RulePool
{
    /**#@+
     * Supported types of fallback rules
     */
    const TYPE_FILE = 'file';
    const TYPE_LOCALE_FILE = 'locale';
    const TYPE_TEMPLATE_FILE = 'template';
    const TYPE_STATIC_FILE = 'static';
    const TYPE_EMAIL_TEMPLATE = 'email';
    /**#@-*/

    /**
     * File system
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    private $rules = [];

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(Filesystem $filesystem, ComponentRegistrar $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
        $this->filesystem = $filesystem;
    }

    /**
     * Retrieve newly created fallback rule for locale files, such as CSV translation maps
     *
     * @return RuleInterface
     */
    protected function createLocaleFileRule()
    {
        $themesDir = $this->filesystem->getDirectoryRead(DirectoryList::THEMES)->getAbsolutePath();
        return new Theme(
            new Simple("$themesDir/<area>/<theme_path>")
        );
    }

    /**
     * Creates Rules using all module directories
     *
     * @param $pattern
     * @param array $optionalParams
     * @return Simple[]
     */
    private function createModuleRules($pattern, $optionalParams = [])
    {
        $rules = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $modulePath) {
            $rules[] = new Simple($modulePath . '/' . $pattern, $optionalParams);
        }
        return $rules;
    }

    /**
     * Retrieve newly created fallback rule for template files
     *
     * @return RuleInterface
     */
    protected function createTemplateFileRule()
    {
        $themesDir = $this->filesystem->getDirectoryRead(DirectoryList::THEMES)->getAbsolutePath();

        return new ModularSwitch(
            new Theme(
                new Simple("$themesDir/<area>/<theme_path>/templates")
            ),
            new Composite(
                array_merge(
                    [new Theme(new Simple("$themesDir/<area>/<theme_path>/<namespace>_<module>/templates"))],
                    $this->createModuleRules("view/<area>/templates"),
                    $this->createModuleRules('view/base/templates')
                )
            )
        );
    }

    /**
     * Retrieve newly created fallback rule for dynamic view files
     *
     * @return RuleInterface
     */
    protected function createFileRule()
    {
        $themesDir = $this->filesystem->getDirectoryRead(DirectoryList::THEMES)->getAbsolutePath();

        return new ModularSwitch(
            new Theme(new Simple("$themesDir/<area>/<theme_path>")),
            new Composite(
                array_merge(
                    [new Theme(new Simple("$themesDir/<area>/<theme_path>/<namespace>_<module>"))],
                    $this->createModuleRules("view/<area>"),
                    $this->createModuleRules('view/base')
                )
            )
        );
    }

    /**
     * Retrieve newly created fallback rule for static view files, such as CSS, JavaScript, images, etc.
     *
     * @return RuleInterface
     */
    protected function createViewFileRule()
    {
        $themesDir = rtrim($this->filesystem->getDirectoryRead(DirectoryList::THEMES)->getAbsolutePath(), '/');
        $libDir = rtrim($this->filesystem->getDirectoryRead(DirectoryList::LIB_WEB)->getAbsolutePath(), '/');
        return new ModularSwitch(
            new Composite(
                [
                    new Theme(
                        new Composite(
                            [
                                new Simple("$themesDir/<area>/<theme_path>/web/i18n/<locale>", ['locale']),
                                new Simple("$themesDir/<area>/<theme_path>/web"),
                            ]
                        )
                    ),
                    new Simple($libDir),
                ]
            ),
            new Composite(
                array_merge(
                    [
                        new Theme(
                            new Composite(
                                [
                                    new Simple(
                                        "$themesDir/<area>/<theme_path>/<namespace>_<module>/web/i18n/<locale>",
                                        ['locale']
                                    ),
                                    new Simple("$themesDir/<area>/<theme_path>/<namespace>_<module>/web"),
                                ]
                            )
                        )
                    ],
                    $this->createModuleRules("view/<area>/web/i18n/<locale>", ['locale']),
                    $this->createModuleRules("view/base/web/i18n/<locale>", ['locale']),
                    $this->createModuleRules("view/<area>/web"),
                    $this->createModuleRules('view/base/web')
                )
            )
        );
    }

    /**
     * Retrieve newly created fallback rule for email templates.
     *
     * Emails are only loaded in a modular context, so a non-modular rule is not specified.
     *
     * @return RuleInterface
     */
    protected function createEmailTemplateFileRule()
    {
        $themesDir = rtrim($this->filesystem->getDirectoryRead(DirectoryList::THEMES)->getAbsolutePath(), '/');

        return new Composite(
            array_merge(
                [new Theme(new Simple("$themesDir/<area>/<theme_path>/<namespace>_<module>/email"))],
                $this->createModuleRules("view/<area>/email")
            )
        );
    }

    /**
     * @param string $type
     * @return RuleInterface
     * @throws \InvalidArgumentException
     */
    public function getRule($type)
    {
        if (isset($this->rules[$type])) {
            return $this->rules[$type];
        }
        switch ($type) {
            case self::TYPE_FILE:
                $rule = $this->createFileRule();
                break;
            case self::TYPE_LOCALE_FILE:
                $rule = $this->createLocaleFileRule();
                break;
            case self::TYPE_TEMPLATE_FILE:
                $rule = $this->createTemplateFileRule();
                break;
            case self::TYPE_STATIC_FILE:
                $rule = $this->createViewFileRule();
                break;
            case self::TYPE_EMAIL_TEMPLATE:
                $rule = $this->createEmailTemplateFileRule();
                break;
            default:
                throw new \InvalidArgumentException("Fallback rule '$type' is not supported");
        }
        $this->rules[$type] = $rule;
        return $this->rules[$type];
    }
}
