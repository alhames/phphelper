<?php

namespace PhpHelper\Tests;

use PhpHelper\Str;

/**
 * Class StrTest.
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

        for ($i = 0; $i < 128; ++$i) {
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

    /**
     *
     */
    public function testIsEmail()
    {
        $this->assertTrue(Str::isEmail('user@domain.com'));
        $this->assertFalse(Str::isEmail('domain.com'));
    }

    /**
     *
     */
    public function testIsHash()
    {
        $this->assertTrue(Str::isHash(md5('test')));
        $this->assertTrue(Str::isHash('1234567890abcdef', 16));
        $this->assertFalse(Str::isHash('1234567890abcdef'));
        $this->assertTrue(Str::isHash('abc', 3));
        $this->assertTrue(Str::isHash(123, 3));
        $this->assertFalse(Str::isHash('xyz', 3));
        $this->assertTrue(Str::isHash(0xFF, 3));
        $this->assertFalse(Str::isHash(['array']));
    }

    public function testPack()
    {
        // todo
    }

    public function testUnpack()
    {
        // todo
    }

    /**
     *
     */
    public function testPad()
    {
        $this->assertEquals('абв   ', Str::pad('абв', 6));
        $this->assertEquals('   абв', Str::pad('абв', 6, ' ', STR_PAD_LEFT));
        $this->assertEquals(' абв  ', Str::pad('абв', 6, ' ', STR_PAD_BOTH));
        $this->assertEquals('абв---', Str::pad('абв', 6, '-'));
        $this->assertEquals('00001', Str::pad(1, 5, 0, STR_PAD_LEFT));
        $this->assertEquals('абвгд', Str::pad('абвгд', 3));
    }

    /**
     * @dataProvider caseProvider
     *
     * @param $string
     * @param $expected
     * @param $convention
     */
    public function testConvertCase($string, $expected, $convention)
    {
        $this->assertEquals($expected, Str::convertCase($string, $convention));
    }

    /**
     * @return array
     */
    public function caseProvider()
    {
        $strings = [
        //   source           camelCase       CamelCase       snake_case        SNAKE_CASE        kebab-case        Kebab-Case
            ['simple',        'simple',       'Simple',       'simple',         'SIMPLE',         'simple',         'Simple'],
            ['two words',     'twoWords',     'TwoWords',     'two_words',      'TWO_WORDS',      'two-words',      'Two-Words'],
            ['some number 1', 'someNumber1',  'SomeNumber1',  'some_number_1',  'SOME_NUMBER_1',  'some-number-1',  'Some-Number-1'],
            ['1 first digit', '1FirstDigit',  '1FirstDigit',  '1_first_digit',  '1_FIRST_DIGIT',  '1-first-digit',  '1-First-Digit'],
            ['me 1 in mid',   'me1InMid',     'Me1InMid',     'me_1_in_mid',    'ME_1_IN_MID',    'me-1-in-mid',    'Me-1-In-Mid'],
            ['HTML',          'html',         'Html',         'html',           'HTML',           'html',           'Html'],
            ['image.jpg',     'imageJpg',     'ImageJpg',     'image_jpg',      'IMAGE_JPG',      'image-jpg',      'Image-Jpg'],
            ['simpleXML',     'simpleXml',    'SimpleXml',    'simple_xml',     'SIMPLE_XML',     'simple-xml',     'Simple-Xml'],
            ['PDFLoad',       'pdfLoad',      'PdfLoad',      'pdf_load',       'PDF_LOAD',       'pdf-load',       'Pdf-Load'],
            ['loadHTMLFile',  'loadHtmlFile', 'LoadHtmlFile', 'load_html_file', 'LOAD_HTML_FILE', 'load-html-file', 'Load-Html-File'],
            ['PHP_INT_MAX',   'phpIntMax',    'PhpIntMax',    'php_int_max',    'PHP_INT_MAX',    'php-int-max',    'Php-Int-Max'],
            ['ICar',          'iCar',         'ICar',         'i_car',          'I_CAR',          'i-car',          'I-Car'],
            ['Disk:C',        'diskC',        'DiskC',        'disk_c',         'DISK_C',         'disk-c',         'Disk-C'],
            ['one_TwoThree',  'oneTwoThree',  'OneTwoThree',  'one_two_three',  'ONE_TWO_THREE',  'one-two-three',  'One-Two-Three'],
            [' _some--MIX-',  'someMix',      'SomeMix',      'some_mix',       'SOME_MIX',       'some-mix',       'Some-Mix'],
            ['UP123low',      'up123Low',     'Up123Low',     'up_123_low',     'UP_123_LOW',     'up-123-low',     'Up-123-Low'],
        ];

        $conventions = [
            null,
            Str::CASE_CAMEL_LOWER,
            Str::CASE_CAMEL_UPPER,
            Str::CASE_SNAKE_LOWER,
            Str::CASE_SNAKE_UPPER,
            Str::CASE_KEBAB_LOWER,
            Str::CASE_KEBAB_UPPER,
        ];

        $data = [];
        $total = count($conventions);

        for ($i = 1; $i < $total; ++$i) {
            foreach ($strings as $string) {
                $data[] = [$string[0], $string[$i], $conventions[$i]];
                for ($j = 1; $j < $total; ++$j) {
                    if ($j !== $i) {
                        $data[] = [$string[$j], $string[$i], $conventions[$i]];
                    }
                }
            }
        }

        return $data;
    }
}
