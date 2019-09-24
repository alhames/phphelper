<?php

/*
 * This file is part of the PHP Helper package.
 *
 * (c) Pavel Logachev <alhames@mail.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpHelper\Image;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\RequestOptions;
use PhpHelper\Hash;
use PhpHelper\Str;
use Psr\Http\Message\StreamInterface;

/**
 * Class Image.
 */
class Image
{
    const TYPE_JPEG = 'jpeg';
    const TYPE_PNG = 'png';
    const TYPE_GIF = 'gif';
    const TYPE_WEBP = 'webp';
    const TYPE_BMP = 'bmp';
    const TYPE_TIFF = 'tiff';
    const TYPE_ICO = 'ico';

    /** @var array Options */
    protected static $options = [
        'max_size' => 10 * 1024 * 1024, // 10 MiB
        'max_width' => 10000, // 10,000 px
        'max_height' => 10000, // 10,000 px
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36', // Google Chrome 76
        'connect_timeout' => 10, // 10 sec
        'timeout' => 15, // 15 sec
        'supported_types' => [self::TYPE_JPEG, self::TYPE_PNG, self::TYPE_GIF, self::TYPE_WEBP], // Supported image types
    ];

    /** @var Client HTTP Client for remote requests. */
    protected static $httpClient;

    /** @var string Path to file. */
    protected $file;
    /** @var string Binary content of image. */
    protected $data;
    /** @var Hash MD5 hash */
    protected $hash;
    /** @var string Mime type of image. */
    protected $mimeType;
    /** @var int File size in bytes. */
    protected $size;
    /** @var int Image width in pixels. */
    protected $width;
    /** @var int Image height in pixels. */
    protected $height;

    /** @var array Existing image mime types. */
    protected static $mimeTypes = [
        'image/png' => self::TYPE_PNG,
        'image/gif' => self::TYPE_GIF,
        'image/webp' => self::TYPE_WEBP,
        'image/jpeg' => self::TYPE_JPEG,
        'image/pjpeg' => self::TYPE_JPEG,
        'image/bmp' => self::TYPE_BMP,
        'image/x-ms-bmp' => self::TYPE_BMP,
        'image/tiff' => self::TYPE_TIFF,
        'image/x-icon' => self::TYPE_ICO,
        'image/vnd.microsoft.icon' => self::TYPE_ICO,
    ];

    /**
     * Image constructor.
     *
     * @param string|null $data
     * @param string|null $file
     */
    protected function __construct(?string $data = null, ?string $file = null)
    {
        $isFile = null !== $file;
        $this->size = $isFile ? filesize($file) : \strlen($data);
        if (static::$options['max_size'] < $this->size) {
            throw new ImageException('The file is too big.', ImageException::ERR_SIZE);
        }

        $fInfo = new \finfo(FILEINFO_MIME_TYPE);
        $this->mimeType = $isFile ? $fInfo->file($file) : $fInfo->buffer($data);
        if (!isset(static::$mimeTypes[$this->mimeType])) {
            throw new ImageException(sprintf('Mime type "%s" is not supported.', $this->mimeType), ImageException::ERR_TYPE);
        }

        $type = static::$mimeTypes[$this->mimeType];
        if (!\in_array($type, static::$options['supported_types'], true)) {
            throw new ImageException(sprintf('Type "%s" is not supported.', $type), ImageException::ERR_TYPE);
        }

        $size = $isFile ? getimagesize($file) : getimagesizefromstring($data);
        if (!$size) {
            throw new ImageException('The file must be an image.', ImageException::ERR_TYPE);
        }
        $this->width = $size[0];
        $this->height = $size[1];
        if (static::$options['max_width'] < $this->width || static::$options['max_height'] < $this->height) {
            $message = sprintf('Max resolution is %dx%d, %dx%d given.', static::$options['max_width'], static::$options['max_height'], $this->width, $this->height);
            throw new ImageException($message, ImageException::ERR_RESOLUTION);
        }

        $this->hash = $isFile ? Hash::createFromMd5File($file) : Hash::createFromMd5($data);

        if ($isFile) {
            $this->file = $file;
        } else {
            $this->data = $data;
        }
    }

    /**
     * @param array options
     */
    public static function setOptions(array $options): void
    {
        static::$options = array_merge(static::$options, $options);
    }

    /**
     * @param string $option
     *
     * @return int|string|array
     */
    public static function getOption(string $option)
    {
        return static::$options[$option];
    }

    /**
     * @param string|\SplFileInfo $path
     *
     * @return static
     */
    public static function createFromFile($path)
    {
        if ($path instanceof \SplFileInfo) {
            if (!$path->isFile()) {
                throw new ImageException('The file does not exists.', ImageException::ERR_PATH);
            }
            if (!$path->isReadable()) {
                throw new ImageException('The file must be readable.', ImageException::ERR_READ);
            }

            $path = $path->getRealPath();
        } elseif (!is_file($path)) {
            throw new ImageException('The file does not exists.', ImageException::ERR_PATH);
        } elseif (!is_readable($path)) {
            throw new ImageException('The file must be readable.', ImageException::ERR_READ);
        }

        return new static(null, $path);
    }

    /**
     * @param string $url
     *
     * @return static
     */
    public static function createFromUrl(string $url)
    {
        if (0 === stripos($url, 'data:')) {
            if (!preg_match('#^data:(//)?[^,]*?(?<base64>;base64)?,(?<data>.+)$#is', $url, $matches)) {
                throw new ImageException('Invalid Data URL.', ImageException::ERR_PATH);
            }

            return new static($matches['base64'] ? base64_decode($matches['data']) : $matches['data']);
        }

        if (0 === stripos($url, '//')) {
            $url = 'https:'.$url;
        }

        if (!Str::isUrl($url, true)) {
            throw new ImageException('Invalid URL.', ImageException::ERR_PATH);
        }

        $options = [
            RequestOptions::HEADERS => [
                'Referer' => parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST).'/',
                'User-Agent' => static::$options['user_agent'],
            ],
            RequestOptions::CONNECT_TIMEOUT => static::$options['connect_timeout'],
            RequestOptions::TIMEOUT => static::$options['timeout'],
        ];
        try {
            $response = static::getHttpClient()->request('GET', $url, $options);
        } catch (GuzzleException $e) {
            throw new ImageException('Can\'t load the image from URL.', ImageException::ERR_READ, $e);
        }

        if (static::$options['max_size'] < $response->getBody()->getSize()) {
            throw new ImageException('The file is too big.', ImageException::ERR_SIZE);
        }

        return new static((string) $response->getBody());
    }

    /**
     * @param string $string
     *
     * @return static
     */
    public static function createFromString(string $string)
    {
        return new static($string);
    }

    /**
     * @return StreamInterface
     */
    public function getStream(): StreamInterface
    {
        return stream_for(null !== $this->file ? fopen($this->file, 'r') : $this->data, ['size' => $this->size]);
    }

    /**
     * @param string      $directory
     * @param string|null $name
     *
     * @return static
     */
    public function save(string $directory, ?string $name = null)
    {
        try {
            if (!is_dir($directory)) {
                if (false === mkdir($directory, 0777, true) && !is_dir($directory)) {
                    throw new ImageException(sprintf('Unable to create the "%s" directory.', $directory), ImageException::ERR_WRITE);
                }
            } elseif (!is_writable($directory)) {
                throw new ImageException(sprintf('Unable to write in the "%s" directory.', $directory), ImageException::ERR_WRITE);
            }

            $path = $directory.\DIRECTORY_SEPARATOR.($name ?? $this->getFullName());
            if (null !== $this->file) {
                copy($this->file, $path);
            } else {
                file_put_contents($path, $this->data);
            }

            return $this;
        } catch (\ErrorException | \Error $e) {
            throw new ImageException($e->getMessage(), ImageException::ERR_WRITE, $e);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->hash->getUriSafeBase64();
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->getName().'.'.$this->getType();
    }

    /**
     * @param bool $rawOutput
     *
     * @return string
     */
    public function getMd5(bool $rawOutput = false): string
    {
        return $rawOutput ? $this->hash->getBinary() : $this->hash->getHex();
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return static::$mimeTypes[$this->mimeType];
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->getType();
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return ClientInterface
     */
    protected static function getHttpClient(): ClientInterface
    {
        if (null === static::$httpClient) {
            static::$httpClient = new Client();
        }

        return static::$httpClient;
    }
}
