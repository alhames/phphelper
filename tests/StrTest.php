<?php

namespace PhpHelper\Tests;

use PhpHelper\Str;

/**
 * Class StrTest
 */
class StrTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider charProvider
     *
     * @param int   $code
     * @param mixed $char
     */
    public function testOrd($code, $char)
    {
        $this->assertEquals($code, Str::ord($char));
    }

    /**
     * @dataProvider charProvider
     *
     * @param int   $code
     * @param mixed $char
     */
    public function testChr($code, $char)
    {
        $this->assertEquals($char, Str::chr($code));
    }

    /**
     * @return array
     */
    public function charProvider()
    {
        $chars = [];

        for ($i = 0; $i < 128; $i++) {
            $chars[] = [$i, chr($i)];
        }

        return array_merge($chars, [
            [0, "\0"],
            [0x9, "\t"],
            [0xA, "\n"],
            [0xD, "\r"],

            [0xA9, '©'],
            [0xC0, 'À'],
            [0xF7, '÷'],
            [0x190, 'Ɛ'],
            [0x3BC, 'μ'],
            [0x410, 'А'],
            [0x44B, 'ы'],
            [0x58D, '֍'],
            [0x1D6B, 'ᵫ'],
            [0x2211, '∑'],
            [0x22C5, '⋅'],
            [0x263A, '☺'],
            [0x2F65, '⽥'],
            [0x3576, '㕶'],
        ]);
    }
}
