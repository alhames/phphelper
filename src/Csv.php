<?php

namespace PhpHelper;

/**
 * Class Csv
 *
 * @see http://www.rfc-editor.org/rfc/rfc4180.txt
 */
class Csv
{
    /** @var string */
    protected static $delimiter = ',';
    /** @var string */
    protected static $enclosure = '"';
    /** @var string */
    protected static $eol = "\r\n";
    /** @var string */
    protected static $charset = 'utf-8';

    /**
     * @param array $data
     * @param array $options
     *
     * @return string
     */
    public static function encode(array $data, array $options = [])
    {
        $delimiter = isset($options['delimiter']) ? $options['delimiter'] : static::$delimiter;
        $enclosure = isset($options['enclosure']) ? $options['enclosure'] : static::$enclosure;
        $eol = isset($options['eol']) ? $options['eol'] : static::$eol;
        $charset = isset($options['charset']) ? $options['charset'] : static::$charset;
        $force = isset($options['force']) ? $options['force'] : false;

        $csv = '';
        foreach ($data as $row) {
            $line = '';
            foreach ($row as $cell) {
                if (
                    $force
                    || mb_strpos($cell, $delimiter, 0, $charset) !== false
                    || mb_strpos($cell, $enclosure, 0, $charset) !== false
                    || mb_strpos($cell, "\r", 0, $charset) !== false
                    || mb_strpos($cell, "\n", 0, $charset) !== false
                ) {
                    if (mb_strpos($cell, $enclosure, 0, $charset) !== false) {
                        $cell = strtr($cell, [$enclosure => $enclosure.$enclosure]);
                    }
                    $cell = $enclosure.$cell.$enclosure;
                }
                $line .= $cell.$delimiter;
            }
            $csv .= mb_substr($line, 0, -1, $charset).$eol;
        }

        return $csv;
    }

    /**
     * @todo Сломается если внутри строки будет eol
     *
     * @param string $csv
     * @param array  $options
     *
     * @return array
     */
    public static function decode($csv, array $options = [])
    {
        $delimiter = isset($options['delimiter']) ? $options['delimiter'] : static::$delimiter;
        $enclosure = isset($options['enclosure']) ? $options['enclosure'] : static::$enclosure;
        $eol = isset($options['eol']) ? $options['eol'] : static::$eol;

        $data = [];
        $rows = explode($eol, rtrim($csv, $eol));

        foreach ($rows as $row) {
            $data[] = str_getcsv($row, $delimiter, $enclosure);
        }

        return $data;
    }
}
