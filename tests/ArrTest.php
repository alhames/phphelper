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

use PhpHelper\Arr;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ArrTest.
 */
class ArrTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider groupByColumnDataProvider
     *
     * @param array $expected
     * @param array $array
     * @param       $keyColumn
     * @param null  $valueColumn
     */
    public function testGroupByColumn(array $expected, array $array, $keyColumn, $valueColumn = null)
    {
        $this->assertSame($expected, Arr::groupByColumn($array, $keyColumn, $valueColumn));
    }

    /**
     * @return array
     */
    public function groupByColumnDataProvider()
    {
        return self::getData('groupByColumn');
    }

    /**
     * @dataProvider columnToKeyDataProvider
     *
     * @param array $expected
     * @param array $array
     * @param       $keyColumn
     * @param null  $valueColumn
     */
    public function testColumnToKey(array $expected, array $array, $keyColumn, $valueColumn = null)
    {
        $this->assertSame($expected, Arr::columnToKey($array, $keyColumn, $valueColumn));
    }

    /**
     * @return array
     */
    public function columnToKeyDataProvider()
    {
        return self::getData('columnToKey');
    }

    /**
     * @dataProvider searchByColumnValueDataProvider
     *
     * @param array|null $expected
     * @param array      $array
     * @param            $column
     * @param            $value
     * @param bool       $strict
     */
    public function testSearchByColumnValue($expected, array $array, $column, $value, bool $strict = false)
    {
        $this->assertSame($expected, Arr::searchByColumnValue($array, $column, $value, $strict));
    }

    /**
     * @return array
     */
    public function searchByColumnValueDataProvider()
    {
        return self::getData('searchByColumnValue');
    }

    /**
     * @dataProvider getValueByPathDataProvider
     *
     * @param        $expected
     * @param array  $array
     * @param string $path
     * @param string $delimiter
     */
    public function testGetValueByPath($expected, array $array, string $path, string $delimiter = '/')
    {
        $this->assertSame($expected, Arr::getValueByPath($array, $path, $delimiter));
    }

    /**
     * @return array
     */
    public function getValueByPathDataProvider()
    {
        return self::getData('getValueByPath');
    }

    /**
     * @dataProvider reduceAssocDataProvider
     *
     * @param          $expected
     * @param          $array
     * @param callable $callback
     * @param null     $initial
     */
    public function testReduceAssoc($expected, $array, callable $callback, $initial = null)
    {
        $this->assertSame($expected, Arr::reduceAssoc($array, $callback, $initial));
    }

    /**
     * @return array
     */
    public function reduceAssocDataProvider()
    {
        $toString = function ($carry, $item, $key) {
            return sprintf('%s%s => %s;', $carry, $key, $item);
        };
        $wrap = function ($carry, $item, $key) {
            $carry = $carry ?? [];
            $carry[sprintf('(%s)', $key)] = sprintf('<%s>', $item);

            return $carry;
        };
        $filter = function ($carry, $item, $key) {
            $carry = $carry ?? [];
            if ($key < 3 && $item > 2) {
                $carry[$key] = $item;
            }

            return $carry;
        };

        return [
            [
                'expected' => 'a => 1;b => 2;',
                'array' => ['a' => 1, 'b' => 2],
                'callback' => $toString,
            ],
            [
                'expected' => 'Start:a => 1;b => 2;',
                'array' => ['a' => 1, 'b' => 2],
                'callback' => $toString,
                'initial' => 'Start:',
            ],
            [
                'expected' => ['(a)' => '<1>', '(b)' => '<2>'],
                'array' => ['a' => 1, 'b' => 2],
                'callback' => $wrap,
            ],
            [
                'expected' => [9 => 1, 2 => 3],
                'array' => [1, 2, 3, 4, 5],
                'callback' => $filter,
                'initial' => [9 => 1],
            ],
        ];
    }

    /**
     * @param string $method
     *
     * @return mixed
     */
    private static function getData(string $method)
    {
        $yaml = file_get_contents(__DIR__.'/Fixtures/Arr.yml');
        $data = Yaml::parse($yaml);

        return $data[$method];
    }
}
