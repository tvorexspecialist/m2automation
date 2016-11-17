<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Utility;

/**
 * A helper detects functions
 */
class FunctionDetector
{
    /**
     * Detect functions in given file
     *
     * return result in this format:
     *  [
     *      line_number => [
     *          function_name_1,
     *          function_name_2,
     *          function_name_3,
     *      ],
     *      line_number => [
     *          function_name_1,
     *          function_name_2,
     *          function_name_3,
     *      ],
     *  ]
     *
     * @param string $fileName
     * @param string[] $functions
     * @return array
     */
    public function detectFunctions($fileName, $functions)
    {
        $result = [];
        $regexp = $this->composeRegexp($functions);
        if ($regexp) {
            $lines = preg_grep(
                $regexp,
                file($fileName)
            );
            foreach ($lines as $lineNumber => $line) {
                if (preg_match_all($regexp, $line, $matches)) {
                    $result[$lineNumber] = $matches[1];
                }
            }
        }
        return $result;
    }

    /**
     * Compose regular expression
     *
     * @param array $functions
     * @return string
     */
    private function composeRegexp(array $functions)
    {
        if (empty($functions)) {
            return '';
        }
        return '/(?<!function )\b(' . join('|', $functions) . ')\s*\(/i';
    }
}
