<?php

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
        $json = \json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('json_encode error: '.json_last_error_msg());
        }

        return $json;
    }

    /**
     * @param string $json
     *
     * @return mixed
     */
    public static function decode(string $json)
    {
        $data = \json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('json_decode error: '.json_last_error_msg());
        }

        return $data;
    }
}
