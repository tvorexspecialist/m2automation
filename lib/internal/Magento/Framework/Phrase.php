<?php
/**
 * Phrase (for replacing Data Value with Object)
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Zend\Stdlib\JsonSerializable;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\Phrase\Renderer\Placeholder as RendererPlaceholder;

class Phrase implements JsonSerializable
{
    /**
     * Default phrase renderer. Allows stacking renderers that "don't know about each other"
     *
     * @var RendererInterface
     */
    private static $renderer;

    /**
     * String for rendering
     *
     * @var string
     */
    private $text;

    /**
     * Arguments for placeholder values
     *
     * @var array
     */
    private $arguments;

    /**
     * Set default Phrase renderer
     *
     * @param RendererInterface $renderer
     * @return void
     */
    public static function setRenderer(RendererInterface $renderer)
    {
        self::$renderer = $renderer;
    }

    /**
     * Get default Phrase renderer
     *
     * @return RendererInterface
     */
    public static function getRenderer()
    {
        if (!self::$renderer) {
            self::$renderer = new RendererPlaceholder();
        }
        return self::$renderer;
    }

    /**
     * Phrase construct
     *
     * @param string $text
     * @param array $arguments
     * @throws \InvalidArgumentException
     */
    public function __construct($text, array $arguments = [])
    {
        if (!$text) {
            throw new \InvalidArgumentException(sprintf('Phrase cannot be empty.'));
        }
        $this->text = (string)$text;
        $this->arguments = $arguments;
    }

    /**
     * Get phrase base text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get phrase message arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Render phrase
     *
     * @return string
     */
    public function render()
    {
        try {
            return self::getRenderer()->render([$this->text], $this->getArguments());
        } catch (\Exception $e) {
            return $this->getText();
        }
    }

    /**
     * Defers rendering to the last possible moment (when converted to string)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->render();
    }
}
