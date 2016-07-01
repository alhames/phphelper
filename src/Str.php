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
 * Class Str.
 *
 * @author Pavel Logachev <alhames@mail.ru>
 */
class Str
{
    const FILTER_TEXT = 0b000001;
    const FILTER_HTML = 0b000010;
    const FILTER_CODE = 0b000100;
    const FILTER_PUNCTUATION = 0b001000;
    const FILTER_SPACE = 0b010000;

    const CASE_CAMEL_LOWER = 0b01100;
    const CASE_CAMEL_UPPER = 0b00100;
    const CASE_SNAKE_LOWER = 0b00010;
    const CASE_SNAKE_UPPER = 0b10010;
    const CASE_KEBAB_LOWER = 0b00011;
    const CASE_KEBAB_UPPER = 0b00111;

    /** @var string */
    protected static $filterCodeMask = "[%%%'04X]";

    /** @var array */
    protected static $slugTransliteration = [
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'yo',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'j',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'ts',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'sch',
        'ъ' => '',
        'ы' => 'y',
        'ь' => '',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',
        "'" => '',
    ];

    /** @var string */
    protected static $slugDelimiter = '_';

    /**
     * @param string $char
     *
     * @return int
     */
    public static function ord($char)
    {
        $char = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');

        return unpack('Ncode', $char)['code'];
    }

    /**
     * @param int $code
     *
     * @return string
     */
    public static function chr($code)
    {
        $char = pack('N', $code);

        return mb_convert_encoding($char, 'UTF-8', 'UTF-32BE');
    }

    /**
     * FILTER_TEXT - удаляет все символы, кроме базовых печатных, кирилицы, табуляции и перевода строки
     * FILTER_HTML - удаляет все непечатные базовые символы, кроме табуляции и перевода строки;
     *               замещает все символы на их html-сущности, кроме базовых печатных и кирилицы
     * FILTER_CODE - замещает все символы на их код, кроме базовых печатных и кирилицы
     * FILTER_PUNCTUATION - заменяет все возможные виды дефисов/тире и кавычек на - (x2D) и " (x22) соответсвтенно
     * FILTER_SPACE - заменяет все последовательности пробельных символов на пробел (x20).
     *
     * @param string $string
     * @param int    $mode
     *
     * @return string
     */
    public static function filter($string, $mode = self::FILTER_TEXT)
    {
        // 09: \t
        // 0A: \n
        // 20-7E: Basic latin (1-byte)
        // 400-45F: Cyrillic (2-bytes) (not all)
        // 202E: Right-To-Left Override

        if ($mode & self::FILTER_CODE) {
            return preg_replace_callback(
                '#[^\x20-\x7E\x{400}-\x{45F}]#u',
                function ($data) {
                    return sprintf(static::$filterCodeMask, static::ord($data[0]));
                },
                $string
            );
        }

        if ($mode & self::FILTER_PUNCTUATION) {
            $string = preg_replace('#[\x{2010}-\x{2015}\x{2053}]#u', '-', $string);
            $string = preg_replace('#[\xAB\xBB\x{2018}-\x{201F}\x{2039}\x{203A}]#u', '"', $string);
            $string = preg_replace('#[\x{2116}]#u', '#', $string);
        }

        if ($mode & self::FILTER_SPACE) {
            $string = preg_replace('#\s+#u', ' ', $string);
        }

        if ($mode & self::FILTER_TEXT) {
            return preg_replace('#[^\n\t\x20-\x7E\x{400}-\x{45F}]+#u', '', $string);
        }

        if ($mode & self::FILTER_HTML) {
            $string = preg_replace('#[\x00-\x08\x0B-\x1F\x{202E}]+#u', '', $string);

            return preg_replace_callback(
                '#[^\n\t\x20-\x7E\x{400}-\x{45F}]#u',
                function ($data) {
                    return '&#'.static::ord($data[0]).';';
                },
                $string
            );
        }

        return $string;
    }

    /**
     * @param string $string
     * @param string $characters
     *
     * @return string
     */
    public static function getSlug($string, $characters = '')
    {
        $pattern = '#[^a-z0-9'.static::$slugDelimiter.preg_quote($characters, '#').']+#';
        $string = mb_strtolower($string, 'utf-8');
        $string = strtr($string, static::$slugTransliteration);
        $string = preg_replace($pattern, static::$slugDelimiter, $string);
        $string = preg_replace('#'.static::$slugDelimiter.'{2,}#', static::$slugDelimiter, $string);

        return trim($string, static::$slugDelimiter);
    }

    /**
     * @param int    $length
     * @param string $characters
     *
     * @return string
     */
    public static function getRandomString($length = 32, $characters = 'qwertyuiopasdfghjklzxcvbnm0123456789')
    {
        $max = mb_strlen($characters, 'utf-8') - 1;
        $string = '';

        for ($i = 0; $i < $length; ++$i) {
            $string .= mb_substr($characters, mt_rand(0, $max), 1, 'utf-8');
        }

        return $string;
    }

    /**
     * @todo Вынести кириллицу
     *
     * @param string $url
     * @param bool   $requiredScheme
     *
     * @return bool
     */
    public static function isUrl($url, $requiredScheme = false)
    {
        $pattern = '#^';
        $pattern .= $requiredScheme ? 'https?://' : '((https?:)?//)?'; // scheme
        $pattern .= '([-_a-z0-9а-яё\.]+\.[a-zа-я]{2,10})';             // domain
        $pattern .= '(([-_a-z0-9а-яё\./%\+]+)';                        // path
        $pattern .= '(\?[-_a-z0-9а-яё\./%\+\*"<>&=]*)?)?';             // query
        $pattern .= '$#iu';

        return is_string($url) && preg_match($pattern, $url);
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public static function isEmail($email)
    {
        return is_string($email) && preg_match('#^[^@\s]+@[^@\s]+\.[^@\s]+$#', $email);
    }

    /**
     * @param string $hash
     * @param int    $length
     *
     * @return bool
     */
    public static function isHash($hash, $length = 32)
    {
        return (is_string($hash) || is_int($hash)) && preg_match('#^[0-9a-f]{'.$length.'}$#i', $hash);
    }

    /**
     * @param array $data
     * @param bool  $compressed
     *
     * @return string
     */
    public static function pack(array $data = null, $compressed = false)
    {
        if (empty($data)) {
            return;
        }

        $packedData = serialize($data);

        if ($compressed) {
            $packedData = gzencode($packedData, 9, FORCE_GZIP);
        }

        return $packedData;
    }

    /**
     * @param string $data
     * @param bool   $compressed
     *
     * @return array
     */
    public static function unpack($data, $compressed = false)
    {
        if (empty($data)) {
            return [];
        }

        $unpackedData = $data;

        if ($compressed) {
            $unpackedData = gzdecode($unpackedData);
        }

        return unserialize($unpackedData);
    }

    /**
     * @param string $input
     * @param int    $length
     * @param string $string
     * @param int    $type
     *
     * @return string
     */
    public static function pad($input, $length, $string = ' ', $type = STR_PAD_RIGHT)
    {
        $diff = strlen($input) - mb_strlen($input, 'utf-8');

        return str_pad($input, $length + $diff, $string, $type);
    }

    /**
     * @todo Check args
     *
     * @link https://en.wikipedia.org/wiki/Naming_convention_(programming)
     *
     * @param $string
     * @param $convention
     *
     * @return string
     */
    public static function convertCase($string, $convention)
    {
        $patterns = [
            '#([a-z])([A-Z])#',
            '#([A-Z]+)([A-Z][a-z])#',
            '#([a-z])([0-9])#i',
            '#([0-9])([a-z])#i',
        ];
        $string = preg_replace($patterns, '$1 $2', $string);
        $string = preg_replace('#[^a-z0-9]+#i', ' ', $string);
        $string = trim($string);

        if ($convention & 0b10000) {
            $string = strtoupper($string);
        } else {
            $string = strtolower($string);
        }

        if ($convention & 0b100) {
            $string = ucwords($string);
        }

        if ($convention & 0b10) {
            $replace = $convention & 0b1 ? '-' : '_';
        } else {
            $replace = '';
        }

        $string = str_replace(' ', $replace, $string);

        if ($convention & 0b1000) {
            $string = lcfirst($string);
        }

        return $string;
    }
}
