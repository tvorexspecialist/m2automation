<?php
/**
 * Scan source code for references to classes and see if they indeed exist
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Classes;

class ClassesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * List of already found classes to avoid checking them over and over again
     *
     * @var array
     */
    protected static $_existingClasses = [];

    protected static $_keywordsBlacklist = ["String", "Array", "Boolean", "Element"];

    protected static $_namespaceBlacklist = null;

    protected static $_referenceBlackList = null;

    public function testPhpFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $contents = file_get_contents($file);
                $classes = Classes::getAllMatches(
                    $contents,
                    '/
                # ::getResourceModel ::getBlockSingleton ::getModel ::getSingleton
                \:\:get(?:ResourceModel | BlockSingleton | Model | Singleton)?\(\s*[\'"]([a-z\d\\\\]+)[\'"]\s*[\),]

                # various methods, first argument
                | \->(?:initReport | addBlock | createBlock
                    | setAttributeModel | setBackendModel | setFrontendModel | setSourceModel | setModel
                )\(\s*\'([a-z\d\\\\]+)\'\s*[\),]

                # various methods, second argument
                | \->add(?:ProductConfigurationHelper | OptionsRenderCfg)\(.+?,\s*\'([a-z\d\\\\]+)\'\s*[\),]

                # \Mage::helper ->helper
                | (?:Mage\:\:|\->)helper\(\s*\'([a-z\d\\\\]+)\'\s*\)

                # misc
                | function\s_getCollectionClass\(\)\s+{\s+return\s+[\'"]([a-z\d\\\\]+)[\'"]
                | \'resource_model\'\s*=>\s*[\'"]([a-z\d\\\\]+)[\'"]
                | (?:_parentResourceModelName | _checkoutType | _apiType)\s*=\s*\'([a-z\d\\\\]+)\'
                | \'renderer\'\s*=>\s*\'([a-z\d\\\\]+)\'
                /ix'
                );

                // without modifier "i". Starting from capital letter is a significant characteristic of a class name
                Classes::getAllMatches(
                    $contents,
                    '/(?:\-> | parent\:\:)(?:_init | setType)\(\s*
                    \'([A-Z][a-z\d][A-Za-z\d\\\\]+)\'(?:,\s*\'([A-Z][a-z\d][A-Za-z\d\\\\]+)\')
                    \s*\)/x',
                    $classes
                );

                $this->_collectResourceHelpersPhp($contents, $classes);

                $this->_assertClassesExist($classes, $file);
            },
            \Magento\Framework\App\Utility\Files::init()->getPhpFiles(true, true, true, true, false)
        );
    }

    /**
     * Special case: collect resource helper references in PHP-code
     *
     * @param string $contents
     * @param array &$classes
     */
    protected function _collectResourceHelpersPhp($contents, &$classes)
    {
        $regex = '/(?:\:\:|\->)getResourceHelper\(\s*\'([a-z\d\\\\]+)\'\s*\)/ix';
        $matches = Classes::getAllMatches($contents, $regex);
        foreach ($matches as $moduleName) {
            $classes[] = "{$moduleName}\\Model\\Resource\\Helper\\Mysql4";
        }
    }

    public function testConfigFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $path
             */
            function ($path) {
                $classes = Classes::collectClassesInConfig(simplexml_load_file($path));
                $this->_assertClassesExist($classes, $path);
            },
            \Magento\Framework\App\Utility\Files::init()->getMainConfigFiles()
        );
    }

    public function testLayoutFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $path
             */
            function ($path) {
                $xml = simplexml_load_file($path);

                $classes = Classes::getXmlNodeValues(
                    $xml,
                    '/layout//*[contains(text(), "\\\\Block\\\\") or contains(text(),
                        "\\\\Model\\\\") or contains(text(), "\\\\Helper\\\\")]'
                );
                foreach (Classes::getXmlAttributeValues(
                    $xml,
                    '/layout//@helper',
                    'helper'
                ) as $class) {
                    $classes[] = Classes::getCallbackClass($class);
                }
                foreach (Classes::getXmlAttributeValues(
                    $xml,
                    '/layout//@module',
                    'module'
                ) as $module) {
                    $classes[] = str_replace('_', '\\', "{$module}_Helper_Data");
                }
                $classes = array_merge($classes, Classes::collectLayoutClasses($xml));

                $this->_assertClassesExist(array_unique($classes), $path);
            },
            \Magento\Framework\App\Utility\Files::init()->getLayoutFiles()
        );
    }

    /**
     * Check whether specified classes correspond to a file according PSR-0 standard
     *
     * Cyclomatic complexity is because of temporary marking test as incomplete
     * Suppressing "unused variable" because of the "catch" block
     *
     * @param array $classes
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _assertClassesExist($classes, $path)
    {
        if (!$classes) {
            return;
        }
        $badClasses = [];
        $badUsages = [];
        foreach ($classes as $class) {
            try {
                if (strrchr($class, '\\') === false and !Classes::isVirtual($class)) {
                    $badUsages[] = $class;
                    continue;
                } else {
                    $this->assertTrue(
                        isset(
                            self::$_existingClasses[$class]
                        ) || \Magento\Framework\App\Utility\Files::init()->classFileExists(
                            $class
                        ) || Classes::isVirtual(
                            $class
                        ) || Classes::isAutogenerated(
                            $class
                        )
                    );
                }
                self::$_existingClasses[$class] = 1;
            } catch (\PHPUnit_Framework_AssertionFailedError $e) {
                $badClasses[] = $class;
            }
        }
        if ($badClasses) {
            $this->fail("Files not found for following usages in {$path}:\n" . implode("\n", $badClasses));
        }
        if ($badUsages) {
            $this->fail("Bad usages of classes in {$path}: \n" . implode("\n", $badUsages));
        }
    }

    public function testClassNamespaces()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Assert PHP classes have valid formal namespaces according to file locations
             *
             * @param array $file
             */
            function ($file) {
                $relativePath = str_replace(
                    \Magento\Framework\App\Utility\Files::init()->getPathToSource() . "/",
                    "",
                    $file
                );
                // exceptions made for the files from the blacklist
                self::_setNamespaceBlackList();
                if (in_array($relativePath, self::$_namespaceBlacklist)) {
                    return;
                }

                $contents = file_get_contents($file);

                $classPattern = '/^(abstract\s)?class\s[A-Z][^\s\/]+/m';

                $classNameMatch = [];
                $className = null;

                // if no class declaration found for $file, then skip this file
                if (preg_match($classPattern, $contents, $classNameMatch) == 0) {
                    return;
                }

                $classParts = explode(' ', $classNameMatch[0]);
                $className = array_pop($classParts);
                $this->_assertClassNamespace($file, $relativePath, $contents, $className);
            },
            \Magento\Framework\App\Utility\Files::init()->getClassFiles()
        );
    }

    protected function _setNamespaceBlackList()
    {
        if (!isset(self::$_namespaceBlacklist)) {
            $blackList = [];
            foreach (glob(__DIR__ . '/_files/blacklist/namespace.txt') as $list) {
                $fileList = file($list, FILE_IGNORE_NEW_LINES);
                foreach ($fileList as $currentFile) {
                    $absolutePath = \Magento\Framework\App\Utility\Files::init()->getPathToSource() .
                        '/' .
                        $currentFile;
                    if (is_dir($absolutePath)) {
                        $recursiveFiles = \Magento\Framework\App\Utility\Files::getFiles(
                            [$absolutePath],
                            '*.php',
                            true
                        );
                        $blackList = array_merge($blackList, $recursiveFiles);
                    } else {
                        array_push($blackList, $currentFile);
                    }
                }
            }
            self::$_namespaceBlacklist = $blackList;
        }
    }

    /**
     * Assert PHP classes have valid formal namespaces according to file locations
     *
     *
     * @param string $file
     * @param string $relativePath
     * @param string $contents
     * @param string $className
     */
    protected function _assertClassNamespace($file, $relativePath, $contents, $className)
    {
        $namespacePattern = '/(Magento|Zend)\/[a-zA-Z]+[^\.]+/';
        $formalPattern = '/^namespace\s[a-zA-Z]+(\\\\[a-zA-Z0-9]+)*/m';

        $namespaceMatch = [];
        $formalNamespaceArray = [];
        $namespaceFolders = null;

        // if no namespace pattern found according to the path of the file, skip the file
        if (preg_match($namespacePattern, $relativePath, $namespaceMatch) == 0) {
            return;
        }

        $namespaceFolders = $namespaceMatch[0];
        $classParts = explode('/', $namespaceFolders);
        array_pop($classParts);
        $expectedNamespace = implode('\\', $classParts);

        if (preg_match($formalPattern, $contents, $formalNamespaceArray) != 0) {
            $foundNamespace = substr($formalNamespaceArray[0], 10);
            $foundNamespace = str_replace('\\', '/', $foundNamespace);
            $foundNamespace .= '/' . $className;
            if ($namespaceFolders != null && $foundNamespace != null) {
                $this->assertEquals(
                    $namespaceFolders,
                    $foundNamespace,
                    "Location of {$file} does not match formal namespace: {$expectedNamespace}\n"
                );
            }
        } else {
            $this->fail("Missing expected namespace \"{$expectedNamespace}\" for file: {$file}");
        }
    }

    public function testClassReferences()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $relativePath = str_replace(
                    \Magento\Framework\App\Utility\Files::init()->getPathToSource(),
                    "",
                    $file
                );
                // Due to the examples given with the regex patterns, we skip this test file itself
                if ($relativePath == "/dev/tests/static/testsuite/Magento/Test/Integrity/ClassesTest.php") {
                    return;
                }
                $contents = file_get_contents($file);
                $formalPattern = '/^namespace\s[a-zA-Z]+(\\\\[a-zA-Z0-9]+)*/m';
                $formalNamespaceArray = [];

                // Skip the file if the class is not defined using formal namespace
                if (preg_match($formalPattern, $contents, $formalNamespaceArray) == 0) {
                    return;
                }
                $namespacePath = str_replace('\\', '/', substr($formalNamespaceArray[0], 10));

                // Instantiation of new object, for example: "return new Foo();"
                $newObjectPattern = '/^' .
                    '.*new\s(?<venderClass>\\\\Magento(?:\\\\[a-zA-Z0-9_]+)+)\(.*\)' .
                    '|.*new\s(?<badClass>[A-Z][a-zA-Z0-9]+[a-zA-Z0-9_\\\\]*)\(.*\)\;' .
                    '|use [A-Z][a-zA-Z0-9_\\\\]+ as (?<aliasClass>[A-Z][a-zA-Z0-9]+)' .
                    '/m';
                $result1 = [];
                preg_match_all($newObjectPattern, $contents, $result1);

                // Static function/variable, for example: "Foo::someStaticFunction();"
                $staticCallPattern = '/^' .
                    '((?!Magento).)*(?<venderClass>\\\\Magento(?:\\\\[a-zA-Z0-9_]+)+)\:\:.*\;' .
                    '|[^\\\\^a-z^A-Z^0-9^_^:](?<badClass>[A-Z][a-zA-Z0-9_]+)\:\:.*\;' .
                    '|use [A-Z][a-zA-Z0-9_\\\\]+ as (?<aliasClass>[A-Z][a-zA-Z0-9]+)' .
                    '/m';
                $result2 = [];
                preg_match_all($staticCallPattern, $contents, $result2);

                // Annotation, for example: "* @return \Magento\Foo\Bar" or "* @throws Exception" or "* @return Foo"
                $annotationPattern = '/^' .
                    '[\s]*\*\s\@(?:return|throws)\s(?<venderClass>\\\\Magento(?:\\\\[a-zA-Z0-9_]+)+)' .
                    '|[\s]*\*\s\@return\s(?<badClass>[A-Z][a-zA-Z0-9_\\\\]+)' .
                    '|[\s]*\*\s\@throws\s(?<exception>[A-Z][a-zA-Z0-9_\\\\]+)' .
                    '|use [A-Z][a-zA-Z0-9_\\\\]+ as (?<aliasClass>[A-Z][a-zA-Z0-9]+)' .
                    '/m';
                $result3 = [];
                preg_match_all($annotationPattern, $contents, $result3);

                $vendorClasses = array_unique(
                    array_merge_recursive($result1['venderClass'], $result2['venderClass'], $result3['venderClass'])
                );

                $badClasses = array_unique(
                    array_merge_recursive($result1['badClass'], $result2['badClass'], $result3['badClass'])
                );

                $aliasClasses = array_unique(
                    array_merge_recursive($result1['aliasClass'], $result2['aliasClass'], $result3['aliasClass'])
                );

                $vendorClasses = array_filter($vendorClasses, 'strlen');
                $vendorClasses = $this->referenceBlacklistFilter($vendorClasses);
                if (!empty($vendorClasses)) {
                    $this->_assertClassesExist($vendorClasses, $file);
                }

                if (!empty($result3['exception']) && $result3['exception'][0] != "") {
                    $badClasses = array_merge($badClasses, array_filter($result3['exception'], 'strlen'));
                }

                $badClasses = array_filter($badClasses, 'strlen');
                if (empty($badClasses)) {
                    return;
                }

                $aliasClasses = array_filter($aliasClasses, 'strlen');
                if (!empty($aliasClasses)) {
                    $badClasses = $this->handleAliasClasses($aliasClasses, $badClasses);
                }

                $badClasses = $this->referenceBlacklistFilter($badClasses);
                $badClasses = $this->removeSpecialCases($badClasses, $file, $contents, $namespacePath);
                $this->_assertClassReferences($badClasses, $file);
            },
            \Magento\Framework\App\Utility\Files::init()->getClassFiles()
        );
    }

    /**
     * Remove alias class name references that have been identified as 'bad'.
     *
     * @param $aliasClasses
     * @param $badClasses
     */
    protected function handleAliasClasses($aliasClasses, $badClasses)
    {
        foreach ($aliasClasses as $aliasClass) {
            foreach ($badClasses as $badClass) {
                if (strpos($badClass, $aliasClass) === 0) {
                    unset($badClasses[array_search($badClass, $badClasses)]);
                }
            }
        }
        return $badClasses;
    }

    /**
     * This function is to remove legacy code usages according to _files/blacklist/reference.txt
     * @param $classes
     * @return array
     */
    protected function referenceBlacklistFilter($classes)
    {
        // exceptions made for the files from the blacklist
        self::_setReferenceBlacklist();
        foreach ($classes as $class) {
            if (in_array($class, self::$_referenceBlackList)) {
                unset($classes[array_search($class, $classes)]);
            }
        }
        return $classes;
    }

    protected function _setReferenceBlacklist()
    {
        if (!isset(self::$_referenceBlackList)) {
            $blackList = file(__DIR__ . '/_files/blacklist/reference.txt', FILE_IGNORE_NEW_LINES);
            self::$_referenceBlackList = $blackList;
        }
    }

    /**
     * This function is to remove special cases (if any) from the list of found bad classes
     * @param array $badClasses
     * @param string $file
     * @param string $contents
     * @return array
     */
    protected function removeSpecialCases($badClasses, $file, $contents, $namespacePath)
    {
        foreach ($badClasses as $badClass) {
            // Remove valid usages of Magento modules from the list
            // for example: 'Magento_Sales::actions_edit'
            if (preg_match('/Magento_[A-Z0-9][a-z0-9]*/', $badClass)) {
                unset($badClasses[array_search($badClass, $badClasses)]);
            }

            // Remove usage of key words such as "Array", "String", and "Boolean"
            if (in_array($badClass, self::$_keywordsBlacklist)) {
                unset($badClasses[array_search($badClass, $badClasses)]);
            }

            $classParts = explode('/', $file);
            $className = array_pop($classParts);
            // Remove usage of the class itself from the list
            if ($badClass . '.php' == $className) {
                unset($badClasses[array_search($badClass, $badClasses)]);
            }

            // Remove usage of classes that do NOT using fully-qualified class names (possibly under same namespace)
            $directories = [
                '/app/code/',
                '/lib/internal/',
                '/dev/tools/',
                '/dev/tests/api-functional/framework/',
                '/dev/tests/functional/',
                '/dev/tests/integration/framework/',
                '/dev/tests/integration/framework/tests/unit/testsuite/',
                '/dev/tests/integration/testsuite/',
                '/dev/tests/integration/testsuite/Magento/Test/Integrity/',
                '/dev/tests/static/framework/',
                '/dev/tests/static/testsuite/',
                '/setup/src/',
            ];
            // Full list of directories where there may be namespace classes
            foreach ($directories as $directory) {
                $fullPath = \Magento\Framework\App\Utility\Files::init()->getPathToSource() .
                    $directory .
                    $namespacePath .
                    '/' .
                    str_replace(
                        '\\',
                        '/',
                        $badClass
                    ) . '.php';
                if (file_exists($fullPath)) {
                    unset($badClasses[array_search($badClass, $badClasses)]);
                    break;
                }
            }
            $referenceFile = implode('/', $classParts) . '/' . str_replace('\\', '/', $badClass) . '.php';
            if (file_exists($referenceFile)) {
                unset($badClasses[array_search($badClass, $badClasses)]);
            }

            // Remove usage of classes that have been declared as "use" or "include"
            // Also deals with case like: "use \Zend\Code\Scanner\FileScanner, Magento\Tools\Di\Compiler\Log\Log;"
            // (continued) where there is a comma separating two different classes.
            if (preg_match('/use\s.*[\\n]?.*' . str_replace('\\', '\\\\', $badClass) . '[\,\;]/', $contents)) {
                unset($badClasses[array_search($badClass, $badClasses)]);
            }
        }
        return $badClasses;
    }

    /**
     * Assert any found class name resolves into a file name and corresponds to an existing file
     *
     * @param array $badClasses
     * @param string $file
     */
    protected function _assertClassReferences($badClasses, $file)
    {
        if (empty($badClasses)) {
            return;
        }
        $this->fail("Incorrect namespace usage(s) found in file {$file}:\n" . implode("\n", $badClasses));
    }

    public function testCoversAnnotation()
    {
        $files = \Magento\Framework\App\Utility\Files::init();
        $errors = [];
        foreach ($files->getFiles([BP . '/dev/tests/{integration,unit}'], '*') as $file) {
            $code = file_get_contents($file);
            if (preg_match('/@covers(DefaultClass)?\s+([\w\\\\]+)(::([\w\\\\]+))?/', $code, $matches)) {
                if ($this->isNonexistentEntityCovered($matches)) {
                    $errors[] = $file . ': ' . $matches[0];
                }
            }
        }
        if ($errors) {
            $this->fail(
                'Nonexistent classes/methods were found in @covers annotations: ' . PHP_EOL . implode(PHP_EOL, $errors)
            );
        }
    }

    /**
     * @param array $matches
     * @return bool
     */
    private function isNonexistentEntityCovered($matches)
    {
        return !empty($matches[2]) && !class_exists($matches[2])
            || !empty($matches[4]) && !method_exists($matches[2], $matches[4]);
    }
}
