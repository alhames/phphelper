<?php

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
