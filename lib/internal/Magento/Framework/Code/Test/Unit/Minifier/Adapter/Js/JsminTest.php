<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Minifier\Adapter\Js;

class JShrinkTest extends \PHPUnit_Framework_TestCase
{
    public function testMinify()
    {
        $content = file_get_contents(__DIR__ . '/../../_files/js/original.js');
        $minifier = new \Magento\Framework\Code\Minifier\Adapter\Js\JShrink();
        $actual = $minifier->minify($content);
        $expected = "\nvar one='one';var two='two';";
        $this->assertEquals($expected, $actual);
    }
}
