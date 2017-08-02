<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento translate adapter
 */
namespace Magento\Framework\Translate;

/**
 * Class \Magento\Framework\Translate\Adapter
 *
 * @since 2.0.0
 */
class Adapter extends AbstractAdapter
{
    /**
     * Translate message string.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param array|string $messageId
     * @param null|string $locale
     * @return string
     * @since 2.0.0
     */
    public function translate($messageId, $locale = null)
    {
        $translator = $this->getOptions('translator');
        if (is_callable($translator)) {
            return call_user_func($translator, $messageId);
        } else {
            return $messageId;
        }
    }

    // @codingStandardsIgnoreStart
    /**
     * Translate message string.
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @return string
     * @since 2.0.0
     */
    public function __()
    {
        $args = func_get_args();
        $messageId = array_shift($args);
        $string = $this->translate($messageId);
        if (count($args) > 0) {
            $string = vsprintf($string, $args);
        }
        return $string;
    }
    // @codingStandardsIgnoreEnd
}
