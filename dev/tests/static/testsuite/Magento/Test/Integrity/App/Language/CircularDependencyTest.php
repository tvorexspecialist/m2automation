<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity\App\Language;

use Magento\Framework\App\Language\Config;
use Magento\Framework\Component\ComponentRegistrar;

class CircularDependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config[][]
     */
    private $packs;

    /**
     * Test circular dependencies between languages
     */
    public function testCircularDependencies()
    {
        $componentRegistrar = new ComponentRegistrar();
        $declaredLanguages = $componentRegistrar->getPaths(ComponentRegistrar::LANGUAGE);
        $packs = [];
        foreach ($declaredLanguages as $language) {
            $languageConfig = new Config(file_get_contents($language . '/language.xml'));
            $this->packs[$languageConfig->getVendor()][$languageConfig->getPackage()] = $languageConfig;
            $packs[] = $languageConfig;
        }

        /** @var $languageConfig Config */
        foreach ($packs as $languageConfig) {
            $languages = [];
            /** @var $config Config */
            foreach ($this->collectCircularInheritance($languageConfig) as $config) {
                $languages[] = $config->getVendor() . '/' . $config->getPackage();
            }
            if (!empty($languages)) {
                $this->fail("Circular dependency detected:\n" . implode(' -> ', $languages));
            }
        }
    }

    /**
     * @param Config $languageConfig
     * @param array $languageList
     * @param bool $isCircular
     * @return array|null
     */
    private function collectCircularInheritance(Config $languageConfig, &$languageList = [], &$isCircular = false)
    {
        $packKey = implode('|', [$languageConfig->getVendor(), $languageConfig->getPackage()]);
        if (isset($languageList[$packKey])) {
            $isCircular = true;
        } else {
            $languageList[$packKey] = $languageConfig;
            foreach ($languageConfig->getUses() as $reuse) {
                if (isset($this->packs[$reuse['vendor']][$reuse['package']])) {
                    $this->collectCircularInheritance(
                        $this->packs[$reuse['vendor']][$reuse['package']],
                        $languageList,
                        $isCircular
                    );
                }
            }
        }
        return $isCircular ? $languageList : [];
    }
}
