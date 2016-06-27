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

    public function testFilter()
    {
        // todo
    }

    /**
     * @dataProvider slugProvider
     *
     * @param string $string
     * @param string $slug
     * @param string $characters
     */
    public function testGetSlug($string, $slug, $characters = '')
    {
        $this->assertEquals($slug, Str::getSlug($string, $characters));
    }

    /**
     * @return array
     */
    public function slugProvider()
    {
        return [
            ['абв', 'abv'],
            ['длинное название чего-либо', 'dlinnoe_nazvanie_chego_libo'],
            ['fileName.txt', 'filename_txt'],
            ['fileName.txt', 'filename.txt', '.'],
            ['Заглавная буква в Начале слова и Предложения', 'zaglavnaya_bukva_v_nachale_slova_i_predlozheniya'],
            ['counter-strike', 'counter_strike'],
            ['counter-strike', 'counter-strike', '-'],
            ['@#df$%щф&^жуpor', 'df_schf_zhupor'],
        ];
    }

    public function testGetRandomString()
    {
        // todo
    }

    public function testIsUrl()
    {
        // todo
    }

    public function testIsMail()
    {
        // todo
    }

    public function testIsHash()
    {
        // todo
    }

    public function testPack()
    {
        // todo
    }

    public function testUnpack()
    {
        // todo
    }

    public function testPad()
    {
        // todo
    }

    public function testToCamelCase()
    {
        // todo
    }

    /**
     * @dataProvider underscoreProvider
     *
     * @param string $from
     * @param string $to
     */
    public function testToUnderscore($from, $to)
    {
        $this->assertEquals($to, Str::toUnderscore($from));
    }

    /**
     * @return array
     */
    public function underscoreProvider()
    {
        return [
            ['simpleTest', 'simple_test'],
            ['easy', 'easy'],
            ['HTML', 'html'],
            ['simpleXML', 'simple_xml'],
            ['PDFLoad', 'pdf_load'],
            ['startMIDDLELast', 'start_middle_last'],
            ['AString', 'a_string'],
            ['Some4Numbers234', 'some4_numbers234'],
            ['TEST123String', 'test123_string'],
            ['TEST123string', 'test123string'],
            ['hello__world', 'hello__world'],
            ['hello_world', 'hello_world'],
            ['_hello_world_', '_hello_world_'],
            ['hello_World', 'hello_world'],
            ['HelloWorld', 'hello_world'],
            ['hello-world', 'hello-world'],
            ['myHTMLFiLe', 'my_html_fi_le'],
            ['aBaBaB', 'a_ba_ba_b'],
            ['r_id', 'r_id'],
            ['hello_aBigWorld', 'hello_a_big_world'],
            ['hello_BigWorld', 'hello_big_world'],
            ['libC', 'lib_c'],
        ];
    }
}
