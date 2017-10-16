<?php

/*
 * This file is part of the PHP Helper package.
 *
 * (c) Pavel Logachev <alhames@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpHelper\Tests;

use PhpHelper\HtmlFilter;

/**
 * Class HtmlFilterTest.
 */
class HtmlFilterTest extends \PHPUnit\Framework\TestCase
{
    public function testSimple()
    {
        $htmlFilter = new HtmlFilter([], __DIR__.'/../var');
        $this->assertInstanceOf(HtmlFilter::class, $htmlFilter);
    }
}
