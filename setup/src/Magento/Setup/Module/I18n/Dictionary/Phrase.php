<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary;

/**
 *  Phrase
 */
class Phrase
{
    /**
     * Phrase
     *
     * @var string
     */
    private $_phrase;

    /**
     * Translation
     *
     * @var string
     */
    private $_translation;

    /**
     * Context type
     *
     * @var string
     */
    private $_contextType;

    /**
     * Context value
     *
     * @var array
     */
    private $_contextValue = [];

    /**
     * Phrase construct
     *
     * @param string $phrase
     * @param string $translation
     * @param string|null $contextType
     * @param string|array|null $contextValue
     * @param string|null $quote
     */
    public function __construct($phrase, $translation, $contextType = null, $contextValue = null)
    {
        $this->setPhrase($phrase);
        $this->setTranslation($translation);
        $this->setContextType($contextType);
        $this->setContextValue($contextValue);
    }

    /**
     * Set phrase
     *
     * @param string $phrase
     * @return void
     * @throws \DomainException
     */
    public function setPhrase($phrase)
    {
        if (!$phrase) {
            throw new \DomainException('Missed phrase');
        }
        $this->_phrase = $phrase;
    }

    /**
     * Get phrase
     *
     * @return string
     */
    public function getPhrase()
    {
        return $this->_phrase;
    }

    /**
     * Set translation
     *
     * @param string $translation
     * @return void
     * @throws \DomainException
     */
    public function setTranslation($translation)
    {
        if (!$translation) {
            throw new \DomainException('Missed translation');
        }
        $this->_translation = $translation;
    }

    /**
     * Get translation
     *
     * @return string
     */
    public function getTranslation()
    {
        return $this->_translation;
    }

    /**
     * Set context type
     *
     * @param string $contextType
     * @return void
     */
    public function setContextType($contextType)
    {
        $this->_contextType = $contextType;
    }

    /**
     * Get context type
     *
     * @return string
     */
    public function getContextType()
    {
        return $this->_contextType;
    }

    /**
     * Add context value
     *
     * @param string $contextValue
     * @return void
     * @throws \DomainException
     */
    public function addContextValue($contextValue)
    {
        if (empty($contextValue)) {
            throw new \DomainException('Context value is empty');
        }
        if (!in_array($contextValue, $this->_contextValue)) {
            $this->_contextValue[] = $contextValue;
        }
    }

    /**
     * Set context type
     *
     * @param string $contextValue
     * @return void
     * @throws \DomainException
     */
    public function setContextValue($contextValue)
    {
        if (is_string($contextValue)) {
            $contextValue = explode(',', $contextValue);
        } elseif (null == $contextValue) {
            $contextValue = [];
        } elseif (!is_array($contextValue)) {
            throw new \DomainException('Wrong context type');
        }
        $this->_contextValue = $contextValue;
    }

    /**
     * Get context value
     *
     * @return array
     */
    public function getContextValue()
    {
        return $this->_contextValue;
    }

    /**
     * Get context value as string
     *
     * @param string $separator
     * @return string
     */
    public function getContextValueAsString($separator = ',')
    {
        return implode($separator, $this->_contextValue);
    }

    /**
     * Get VO identifier key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->getPhrase() . '::' . $this->getContextType();
    }

    /**
     * Compile PHP string based on quotes type it enclosed with
     *
     * @return string
     */
    public function getCompiledPhrase()
    {
        return $this->getCompiledString($this->getPhrase());
    }

    /**
     * Compile PHP string based on quotes type it enclosed with
     *
     * @return string
     */
    public function getCompiledTranslation()
    {
        return $this->getCompiledString($this->getTranslation());
    }

    /**
     * Compile PHP string based on quotes type it enclosed with
     *
     * @param string $string
     * @return string
     *
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    private function getCompiledString($string)
    {
        $string = str_replace('$' , '\\$', $string);
        $evalString = 'return "' . str_replace('"', '\\"', $string) . '";';
        $result = @eval($evalString);
        return is_string($result) ? $result :  $string;
    }
}
