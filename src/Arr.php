<?php

/*
 * This file is part of the PHP Helper package.
 *
 * (c) Pavel Logachev <alhames@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpHelper;

/**
 * Class Arr.
 */
class Arr
{
    /**
     * Group array items by column.
     *
     * @param array           $array
     * @param string|int      $keyColumn
     * @param string|int|null $valueColumn Если указан, то в качестве значений будет использоваться только этот столбец
     *
     * @return array
     */
    public static function groupByColumn(array $array, $keyColumn, $valueColumn = null): array
    {
        $result = [];

        foreach ($array as $item) {
            if (!isset($item[$keyColumn])) {
                continue;
            }

            $key = $item[$keyColumn];
            if (!isset($result[$key])) {
                $result[$key] = [];
            }

            if (null !== $valueColumn) {
                $result[$key][] = $item[$valueColumn];
            } else {
                $result[$key][] = $item;
            }
        }

        return $result;
    }

    /**
     * Создает массив, где в качестве ключей используется указанный столбец.
     *
     * @param array      $array
     * @param string|int $keyColumn
     * @param string|int $valueColumn Если указан, то в качестве значений будет использоваться только этот столбец
     *
     * @return array
     */
    public static function columnToKey(array $array, $keyColumn, $valueColumn = null): array
    {
        $keys = array_column($array, $keyColumn);
        $values = null === $valueColumn ? $array : array_column($array, $valueColumn);

        return array_combine($keys, $values);
    }

    /**
     * Возвращает элемент массива, в котором столбец содержит указанное значение.
     *
     * @param array      $array
     * @param string|int $column
     * @param mixed      $value
     * @param bool       $strict
     *
     * @return array|null
     */
    public static function searchByColumnValue(array $array, $column, $value, bool $strict = false)
    {
        foreach ($array as $row) {
            if (!$strict && $row[$column] == $value) {
                return $row;
            } elseif ($strict && $row[$column] === $value) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @see http://php.net/array-reduce
     * @see http://stackoverflow.com/questions/29213170/array-reduce-cant-work-as-associative-array-reducer-for-php
     *
     * @param array|\Traversable $array
     * @param callable           $callback
     * @param mixed              $initial
     *
     * @return mixed
     */
    public static function reduceAssoc($array, callable $callback, $initial = null)
    {
        $carry = $initial;
        foreach ($array as $key => $item) {
            $carry = $callback($carry, $item, $key);
        }

        return $carry;
    }

    /**
     * @param array  $array
     * @param string $path
     * @param string $delimiter
     *
     * @return mixed
     */
    public static function getValueByPath(array $array, string $path, string $delimiter = '/')
    {
        $path = trim($path, $delimiter);
        $keys = $path ? explode($delimiter, $path) : [];
        $value = $array;
        foreach ($keys as $key) {
            $value = $value[$key];
        }

        return $value;
    }
}
