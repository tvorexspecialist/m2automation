<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback\Resolver;

use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\View\Design\FileResolution\Fallback;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Resolver for view files
 */
class Simple implements Fallback\ResolverInterface
{
    /**
     * Directory read factory
     *
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * Fallback factory
     *
     * @var RulePool
     */
    protected $rulePool;

    /**
     * Constructor
     *
     * @param ReadFactory $readFactory
     * @param RulePool $rulePool
     */
    public function __construct(ReadFactory $readFactory, RulePool $rulePool)
    {
        $this->readFactory = $readFactory;
        $this->rulePool = $rulePool;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($type, $file, $area = null, ThemeInterface $theme = null, $locale = null, $module = null)
    {
        self::assertFilePathFormat($file);

        $params = ['area' => $area, 'theme' => $theme, 'locale' => $locale];
        foreach ($params as $key => $param) {
            if ($param === null) {
                unset($params[$key]);
            }
        }
        if (!empty($module)) {
            $params['module_name'] = $module;
        }
        $path = $this->resolveFile($this->rulePool->getRule($type), $file, $params);

        return $path;
    }

    /**
     * Validate the file path format
     *
     * @param string $filePath
     * @throws \InvalidArgumentException
     * @return void
     */
    public static function assertFilePathFormat($filePath)
    {
        if (strpos(str_replace('\\', '/', $filePath), './') !== false) {
            throw new \InvalidArgumentException("File path '{$filePath}' is forbidden for security reasons.");
        }
    }

    /**
     * Get path of file after using fallback rules
     *
     * @param RuleInterface $fallbackRule
     * @param string $file
     * @param array $params
     * @return string|bool
     */
    protected function resolveFile(RuleInterface $fallbackRule, $file, array $params = [])
    {
        foreach ($fallbackRule->getPatternDirs($params) as $dir) {
            $path = "{$dir}/{$file}";
            $dirRead = $this->readFactory->create($dir);
            if ($dirRead->isExist($file)) {
                return $path;
            }
        }
        return false;
    }
}
