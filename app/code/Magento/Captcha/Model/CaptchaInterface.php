<?php
/**
 * Captcha interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model;

/**
 * Captcha Model Interface
 *
 * @api
 */
interface CaptchaInterface
{
    /**
     * Generates captcha
     *
     * @abstract
     * @return void
     */
    public function generate();

    /**
     * Checks whether word entered by user corresponds to the one generated by generate()
     *
     * @param string $word
     * @return bool
     * @abstract
     */
    public function isCorrect($word);

    /**
     * Get Block Name
     * @return string
     */
    public function getBlockName();
}
