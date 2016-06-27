<?php

namespace PhpHelper;

/**
 * Class Str
 */
class Str
{
    const FILTER_TEXT = 0b000001;
    const FILTER_HTML = 0b000010;
    const FILTER_CODE = 0b000100;
    const FILTER_PUNCTUATION = 0b001000;
    const FILTER_SPACE = 0b010000;

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
     * FILTER_SPACE - заменяет все последовательности пробельных символов на пробел (x20)
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

        for ($i = 0; $i < $length; $i++) {
            $string .= mb_substr($characters, mt_rand(0, $max), 1, 'utf-8');
        }

        return $string;
    }

    /**
     * @param string $url
     * @param bool   $requiredScheme
     *
     * @return bool
     */
    public static function isUrl($url, $requiredScheme = false)
    {
        $pattern = '#^'
            .($requiredScheme ? 'https?://' : '((https?:)?//)?') // scheme
            .'([-_a-z0-9а-яё\.]+\.[a-zа-я]{2,10})' // domain
            .'(([-_a-z0-9а-яё\./%\+]+)' // path
            .'(\?[-_a-z0-9а-яё\./%\+\*"<>&=]*)?)?' // query
            .'$#iu';

        return is_string($url) && preg_match($pattern, $url);
    }

    /**
     * @param string $mail
     *
     * @return bool
     */
    public static function isMail($mail)
    {
        return is_string($mail) && preg_match('#^[^@\s]+@[^@\s]+\.[^@\s]+$#', $mail);
    }

    /**
     * @param string $hash
     * @param int    $length
     *
     * @return bool
     */
    public static function isHash($hash, $length = 32)
    {
        return is_string($hash) && preg_match('#^[0-9a-f]{'.$length.'}$#i', $hash);
    }

    /**
     * @param array $data
     * @param bool  $compressed
     *
     * @return null|string
     */
    public static function pack(array $data = null, $compressed = false)
    {
        if (empty($data)) {
            return null;
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
     * @param string $string
     *
     * @return string
     */
    public static function toCamelCase($string)
    {
        $string = preg_replace('#[^a-z0-9]+#i', ' ', $string);
        $string = ucwords($string);

        return str_replace(' ', '', $string);
    }

    /**
     * @link http://stackoverflow.com/a/35719689/1378653
     *
     * @param string $string
     *
     * @return string
     */
    public static function toUnderscore($string)
    {
        $string = preg_replace(['#([a-z0-9])([A-Z])#', '#([A-Z]+)([A-Z][a-z])#'], '$1_$2', $string);

        return strtolower($string);
    }
}
