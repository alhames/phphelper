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
 * Class Json.
 */
class Json
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public static function encode($value): string
    {
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('json_encode error: '.json_last_error_msg());
        }

        return $json;
    }

    /**
     * @param string $json
     * @param bool   $asArray
     *
     * @return mixed
     */
    public static function decode(string $json, bool $asArray = true)
    {
        $data = json_decode($json, $asArray);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('json_decode error: '.json_last_error_msg());
        }

        return $data;
    }

    /**
     * @param $value
     */
    public static function dump($value): void
    {
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('json_encode error: '.json_last_error_msg());
        }

        echo $json;
    }
}
