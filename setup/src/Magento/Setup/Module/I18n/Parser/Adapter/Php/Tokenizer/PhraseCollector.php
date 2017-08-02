<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;

use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;

/**
 * PhraseCollector
 * @since 2.0.0
 */
class PhraseCollector
{
    /**
     * @var \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer
     * @since 2.0.0
     */
    protected $_tokenizer;

    /**
     * Phrases
     *
     * @var array
     * @since 2.0.0
     */
    protected $_phrases = [];

    /**
     * Processed file
     *
     * @var \SplFileInfo
     * @since 2.0.0
     */
    protected $_file;

    /**
     * Are the Phrase objects are parsed as well
     *
     * @var bool
     * @since 2.0.0
     */
    protected $includeObjects = false;

    /**
     * The class name of the phrase object
     * @since 2.0.0
     */
    protected $className = 'Phrase';

    /**
     * Construct
     *
     * @param Tokenizer $tokenizer
     * @param bool $includeObjects
     * @param String $className
     * @since 2.0.0
     */
    public function __construct(Tokenizer $tokenizer, $includeObjects = false, $className = 'Phrase')
    {
        $this->_tokenizer = $tokenizer;
        $this->includeObjects = $includeObjects;
        $this->className = $className;
    }

    /**
     * Get phrases for given file
     *
     * @return array
     * @since 2.0.0
     */
    public function getPhrases()
    {
        return $this->_phrases;
    }

    /**
     * Parse given files for phrase
     *
     * @param string $file
     * @return void
     * @since 2.0.0
     */
    public function parse($file)
    {
        $this->_phrases = [];
        $this->_file = $file;
        $this->_tokenizer->parse($file);
        while (!$this->_tokenizer->isEndOfLoop()) {
            $this->_extractPhrases();
        }
    }

    /**
     * Extract phrases from given tokens. e.g.: __('phrase', ...)
     *
     * @return void
     * @since 2.0.0
     */
    protected function _extractPhrases()
    {
        if ($firstToken = $this->_tokenizer->getNextRealToken()) {
            if (!$this->extractMethodPhrase($firstToken) && $this->includeObjects) {
                $this->extractObjectPhrase($firstToken);
            }
        }
    }

    /**
     * @param Token $firstToken
     * @return bool
     * @since 2.0.0
     */
    protected function extractMethodPhrase(Token $firstToken)
    {
        if ($firstToken->isEqualFunction('__')) {
            $secondToken = $this->_tokenizer->getNextRealToken();
            if ($secondToken && $secondToken->isOpenBrace()) {
                $arguments = $this->_tokenizer->getFunctionArgumentsTokens();
                $phrase = $this->_collectPhrase(array_shift($arguments));
                if (null !== $phrase) {
                    $this->_addPhrase($phrase, count($arguments), $this->_file, $firstToken->getLine());
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param Token $firstToken
     * @return bool
     * @since 2.0.0
     */
    protected function extractObjectPhrase(Token $firstToken)
    {
        if ($firstToken->isNew() && $this->_tokenizer->isMatchingClass($this->className)) {
            $arguments = $this->_tokenizer->getFunctionArgumentsTokens();
            $phrase = $this->_collectPhrase(array_shift($arguments));
            if (null !== $phrase) {
                $this->_addPhrase($phrase, count($arguments), $this->_file, $firstToken->getLine());
                return true;
            }
        }
        return false;
    }

    /**
     * Collect all phrase parts into string. Return null if phrase is a variable
     *
     * @param array $phraseTokens
     * @return string|null
     * @since 2.0.0
     */
    protected function _collectPhrase($phraseTokens)
    {
        $phrase = [];
        if ($phraseTokens) {
            /** @var \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token $phraseToken */
            foreach ($phraseTokens as $phraseToken) {
                if ($phraseToken->isConstantEncapsedString() || $phraseToken->isConcatenateOperator()) {
                    $phrase[] = $phraseToken->getValue();
                }
            }
            if ($phrase) {
                return implode(' ', $phrase);
            }
        }
        return null;
    }

    /**
     * Add phrase
     *
     * @param string $phrase
     * @param int $argumentsAmount
     * @param \SplFileInfo $file
     * @param int $line
     * @return void
     * @since 2.0.0
     */
    protected function _addPhrase($phrase, $argumentsAmount, $file, $line)
    {
        $this->_phrases[] = [
            'phrase' => $phrase,
            'arguments' => $argumentsAmount,
            'file' => $file,
            'line' => $line,
        ];
    }

    /**
     * @param bool $includeObjects
     * @return $this
     * @since 2.0.0
     */
    public function setIncludeObjects($includeObjects = true)
    {
        $this->includeObjects = (bool)$includeObjects;
        return $this;
    }
}
