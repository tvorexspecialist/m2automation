<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Declaration;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraint;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;
use Magento\Framework\Setup\Declaration\Schema\SchemaConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class WhitelistDeclarationTest
 */
class WhitelistDeclarationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;
    /**
     * @var Schema
     */
    private $declarativeSchema;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $schemaConfig = $objectManager->get(SchemaConfigInterface::class);
        $this->declarativeSchema = $schemaConfig->getDeclarationConfig();
        $this->componentRegistrar = $objectManager->get(ComponentRegistrarInterface::class);
    }

    /**
     * Checks that all declared table elements also declared into whitelist declaration.
     *
     * @throws \Exception
     */
    public function testConstraintsAndIndexesAreWhitelisted()
    {
        $undeclaredElements = [];
        $resultMessage = "New table elements that do not exist in the whitelist declaration:\n";
        $whitelistTables = $this->getWhiteListTables();

        foreach ($this->declarativeSchema->getTables() as $schemaTable) {
            $tableNameWithoutPrefix = $schemaTable->getNameWithoutPrefix();
            foreach ($schemaTable->getConstraints() as $constraint) {
                $constraintNameWithoutPrefix = $constraint->getNameWithoutPrefix();
                if (isset($whitelistTables[$tableNameWithoutPrefix][Constraint::TYPE][$constraintNameWithoutPrefix])) {
                    continue;
                }

                $undeclaredElements[$tableNameWithoutPrefix][Constraint::TYPE][] = $constraintNameWithoutPrefix;
            }

            foreach ($schemaTable->getIndexes() as $index) {
                $indexNameWithoutPrefix = $index->getNameWithoutPrefix();
                if (isset($whitelistTables[$tableNameWithoutPrefix][Index::TYPE][$indexNameWithoutPrefix])) {
                    continue;
                }

                $undeclaredElements[$tableNameWithoutPrefix][Index::TYPE][] = $indexNameWithoutPrefix;
            }
        }

        $undeclaredElements = $this->filterUndeclaredElements($undeclaredElements);

        if (!empty($undeclaredElements)) {
            $resultMessage .= json_encode($undeclaredElements, JSON_PRETTY_PRINT);
        }

        $this->assertEmpty($undeclaredElements, $resultMessage);
    }

    /**
     * Excludes ignored elements from the list of undeclared table elements.
     *
     * @param array $undeclaredElements
     * @return array
     */
    private function filterUndeclaredElements(array $undeclaredElements): array
    {
        $ignoredElements = json_decode(file_get_contents(__DIR__ . '/_files/ignore_whitelisting.json'), true);

        return $this->arrayRecursiveDiff($undeclaredElements, $ignoredElements);
    }

    /**
     * Performs a recursive comparison of two arrays.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function arrayRecursiveDiff(array $array1, array $array2): array
    {
        $diffResult = [];

        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    $recursiveDiffResult = $this->arrayRecursiveDiff($value, $array2[$key]);
                    if (count($recursiveDiffResult)) {
                        $diffResult[$key] = $recursiveDiffResult;
                    }
                } else {
                    if (!in_array($value, $array2)) {
                        $diffResult[] = $value;
                    }
                }
            } else {
                $diffResult[$key] = $value;
            }
        }

        return $diffResult;
    }

    /**
     * @return array
     */
    private function getWhiteListTables(): array
    {
        $whiteListTables = [];

        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $path) {
            $whiteListPath = $path . DIRECTORY_SEPARATOR . 'etc' .
                DIRECTORY_SEPARATOR . 'db_schema_whitelist.json';

            if (file_exists($whiteListPath)) {
                $whiteListTables = array_replace_recursive(
                    $whiteListTables,
                    json_decode(file_get_contents($whiteListPath), true)
                );
            }
        }

        return $whiteListTables;
    }
}
