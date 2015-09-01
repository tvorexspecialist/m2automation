<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Utility;

/**
 * A helper find not escaped output in phtml templates
 */
class XssOutputValidator
{
    const ESCAPE_NOT_VERIFIED_PATTERN = '/\* @escapeNotVerified \*/';

    const ESCAPED_PATTERN = '/\* @noEscape \*/';

    /**
     * Store origin for replacements
     * @var array
     */
    private $origins = [];

    /**
     * Store replacements
     * @var array
     */
    private $replacements = [];

    /**
     *
     * @param string $file
     * @return string
     */
    public function getLinesWithXssSensitiveOutput($file)
    {
        $fileContent = file_get_contents($file);
        $xssUnsafeBlocks = $this->getXssUnsafeBlocks($fileContent);

        $lines = [];
        foreach ($xssUnsafeBlocks as $block) {
            $lines = array_merge($lines, $this->findBlockLineNumbers($block, $fileContent));
        }

        if (count($lines)) {
            $lines = array_unique($lines);
            sort($lines);
            return implode(',', $lines);
        }

        return '';
    }

    /**
     * Find block line numbers
     *
     * @param string $block
     * @param string $content
     * @return array
     */
    private function findBlockLineNumbers($block, $content)
    {
        $results = [];
        $pos = strpos($content, $block, 0);
        while ($pos !== false) {
            $contentBeforeString = substr($content, 0, $pos);
            if ($this->isNotEscapeMarkedBlock($contentBeforeString)
                && $this->isNotInCommentBlock($contentBeforeString)
            ) {
                $results[] = count(explode(PHP_EOL, $contentBeforeString));
            }
            $pos = strpos($content, $block, $pos + 1);
        }

        return $results;
    }

    /**
     * Get XSS unsafe output blocks
     *
     * @param string $fileContent
     * @return array
     */
    public function getXssUnsafeBlocks($fileContent)
    {
        $results = [];

        $fileContent = $this->replacePhpQuoteWithPlaceholders($fileContent);
        $fileContent = $this->replacePhpCommentsWithPlaceholders($fileContent);

        $this->addOriginReplacement('\'\'', '-*=single=*-');
        $this->addOriginReplacement('""', '-*=double=*-');

        if (preg_match_all('/<[?]php(.*?)[?]>/sm', $fileContent, $phpBlockMatches)) {
            foreach ($phpBlockMatches[1] as $phpBlock) {
                $phpCommands = explode(';', $phpBlock);
                $echoCommands = preg_grep('#( |^|/\*.*?\*/)echo[\s(]+.*#sm', $phpCommands);
                $results = array_merge(
                    $results,
                    $this->getEchoUnsafeCommands($echoCommands)
                );
            }
        }

        $this->clearOriginReplacements();
        $results = array_unique($results);

        return $results;
    }

    /**
     * @param array $echoCommands
     * @return array
     */
    private function getEchoUnsafeCommands(array $echoCommands)
    {
        $results = [];
        foreach ($echoCommands as $echoCommand) {
            if ($this->isNotEscapeMarkedCommand($echoCommand)) {
                $echoCommand = preg_replace('/^(.*?)echo/sim', 'echo', $echoCommand);
                $xssUnsafeCommands = array_filter(
                    explode('.', ltrim($echoCommand, 'echo')),
                    [$this, 'isXssUnsafeCommand']
                );
                if (count($xssUnsafeCommands)) {
                    $results[] = str_replace(
                        $this->getReplacements(),
                        $this->getOrigins(),
                        $echoCommand
                    );
                }
            }
        }

        return $results;
    }

    /**
     * @param string $contentBeforeString
     * @return bool
     */
    private function isNotEscapeMarkedBlock($contentBeforeString)
    {
        return !preg_match(
            '%('. self::ESCAPE_NOT_VERIFIED_PATTERN . '|' . self::ESCAPED_PATTERN. ')$%sim',
            trim($contentBeforeString)
        );
    }

    /**
     * @param string $contentBeforeString
     * @return bool
     */
    private function isNotInCommentBlock($contentBeforeString)
    {
        $contentBeforeString = explode('<?php', $contentBeforeString);
        $contentBeforeString = preg_replace(
            '%/\*.*?\*/%si',
            '',
            end($contentBeforeString)
        );

        return (strpos($contentBeforeString, '/*') === false);
    }

    /**
     * @param string $command
     * @return bool
     */
    private function isNotEscapeMarkedCommand($command)
    {
        return !preg_match(
            '%' . self::ESCAPE_NOT_VERIFIED_PATTERN . '|'. self::ESCAPED_PATTERN . '%sim',
            $command
        );
    }

    /**
     * Check if command is xss unsafe
     *
     * @param string $command
     * @return bool
     */
    public function isXssUnsafeCommand($command)
    {
        $command = trim($command);
        $cutCommand = strpos($command, '(') !== false ? substr($command, 0, strpos($command, '(') + 1) : $command;

        switch (true)
        {
            case preg_match('/->(escapeUrl|escapeQuote|escapeXssInUrl|.*html.*)\(/simU', $cutCommand):
                return false;
            case preg_match('/^\((int|bool)\)/sim', $command):
                return false;
            case preg_match('/^count\(/sim', $command):
                return false;
            default:
                return true;
        }
    }

    /**
     * @param string $fileContent
     * @return string
     */
    private function replacePhpQuoteWithPlaceholders($fileContent)
    {
        $origins = [];
        $replacements = [];
        if (preg_match_all('/<[?]php(.*?)[?]>/sm', $fileContent, $phpBlockMatches)) {
            foreach ($phpBlockMatches[1] as $phpBlock) {

                $phpBlockQuoteReplaced = preg_replace(
                    ['/([^\\\\])\'\'/si', '/([^\\\\])""/si'],
                    ['\1-*=single=*-', '\1-*=double=*-'],
                    $phpBlock
                );

                $this->addQuoteOriginsReplacements($phpBlockQuoteReplaced);

                $origins[] = $phpBlock;
                $replacements[]  = str_replace(
                    $this->getOrigins(),
                    $this->getReplacements(),
                    $phpBlockQuoteReplaced
                );
            }
        }

        return str_replace($origins, $replacements, $fileContent);
    }

    /**
     * @param string $fileContent
     * @return string
     */
    private function replacePhpCommentsWithPlaceholders($fileContent)
    {
        $origins= [];
        $replacements = [];
        if (preg_match_all('%/\*.*?\*/%simu', $fileContent, $docCommentMatches, PREG_SET_ORDER)) {
            foreach ($docCommentMatches as $docCommentMatch) {
                if ($this->isNotEscapeMarkedCommand($docCommentMatch[0])
                    && !$this->issetOrigin($docCommentMatch[0])) {
                    $origin = $docCommentMatch[0];
                    $replacement = '-*!' . count($this->getOrigins()) . '!*-';
                    $origins[] = $origin;
                    $replacements[] = $replacement;
                    $this->addOriginReplacement(
                        $origin,
                        $replacement
                    );
                }
            }
        }

        return str_replace($origins, $replacements, $fileContent);
    }

    /**
     * Add replacements for expressions in single and double quotes
     *
     * @param string $phpBlock
     * @return void
     */
    private function addQuoteOriginsReplacements($phpBlock)
    {
        $patterns = [
            '/([^\\\\])(["])(.*?)([^\\\\])(["])/sim',
            '/([^\\\\])([\'])(.*?)([^\\\\])([\'])/sim'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $phpBlock, $quoteMatches, PREG_SET_ORDER)) {
                foreach ($quoteMatches as $quoteMatch) {
                    $origin = $quoteMatch[2] . $quoteMatch[3] . $quoteMatch[4] . $quoteMatch[5];
                    if (!$this->issetOrigin($origin)) {
                        $this->addOriginReplacement(
                            $origin,
                            $quoteMatch[2] . '-*=' . count($this->getOrigins()) . '=*-' . $quoteMatch[5]
                        );
                    }
                }
            }
        }
    }

    /**
     * @param string $origin
     * @param string $replacement
     * @return void
     */
    private function addOriginReplacement($origin, $replacement)
    {
        $this->origins[] = $origin;
        $this->replacements[] = $replacement;
    }

    /**
     * Clear origins and replacements
     *
     * @return void
     */
    private function clearOriginReplacements()
    {
        $this->origins = [];
        $this->replacements = [];
    }

    /**
     * @return array
     */
    private function getOrigins()
    {
        return $this->origins;
    }

    /**
     * @param string $origin
     * @return bool
     */
    private function issetOrigin($origin)
    {
        return in_array($origin, $this->origins);
    }

    /**
     * @return array
     */
    private function getReplacements()
    {
        return $this->replacements;
    }
}
