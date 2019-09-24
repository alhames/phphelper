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
 * Class Hash.
 */
class Hash
{
    /** @var string Binary value */
    protected $value;

    /**
     * Hash constructor.
     *
     * @param string $value Binary value.
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @param string $data
     *
     * @return static
     */
    public static function createFromBits(string $data)
    {
        $binary = '';
        foreach (str_split($data, 8) as $byte) {
            $binary .= \chr(\intval($byte, 2));
        }

        return new static($binary);
    }

    /**
     * @param mixed $data
     *
     * @return static
     */
    public static function createFromMd5($data)
    {
        return new static(md5($data, true));
    }

    /**
     * @param string $path
     *
     * @return static
     */
    public static function createFromMd5File(string $path)
    {
        return new static(md5_file($path, true));
    }

    /**
     * @param string $data
     *
     * @return static
     */
    public static function createFromHex(string $data)
    {
        return new static(hex2bin($data));
    }

    /**
     * @return string
     */
    public function getHex(): string
    {
        return bin2hex($this->value);
    }

    /**
     * @return string
     */
    public function getBits(): string
    {
        $bits = '';
        foreach (str_split($this->value) as $byte) {
            $bits .= sprintf('%08b', \ord($byte));
        }

        return $bits;
    }

    /**
     * @return string
     */
    public function getBase64(): string
    {
        return base64_encode($this->value);
    }

    /**
     * @return string
     */
    public function getUriSafeBase64(): string
    {
        return strtr($this->getBase64(), ['+' => '-', '/' => '_', '=' => '']);
    }

    /**
     * @return string
     */
    public function getBinary(): string
    {
        return $this->value;
    }
}
