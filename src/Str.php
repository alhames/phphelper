<?php

namespace PhpHelper;

/**
 * Class Str
 */
class Str
{
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
}
