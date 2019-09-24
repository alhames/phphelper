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

use function GuzzleHttp\Psr7\stream_for;
use PhpHelper\Hash;
use Psr\Http\Message\StreamInterface;

/**
 * Class EditableImage.
 */
class EditableImage extends Image
{
    /** @var string */
    protected $newType;
    /** @var int */
    protected $quality;
    /** @var int */
    protected $newWidth;
    /** @var int */
    protected $newHeight;

    /** @var bool Enable progressive jpeg */
    protected static $interlace = true;
    /** @var array RGB color of background */
    protected static $background = [255, 255, 255];

    /**
     * @param string|null $type
     *
     * @return static
     */
    public function setType(?string $type = null)
    {
        if (null === $type || static::$mimeTypes[$this->mimeType] === $type) {
            $this->newType = null;
        } else {
            if (!\in_array($type, static::$options['supported_types'], true)) {
                throw new \InvalidArgumentException(sprintf('The type "%s" is not supported.', $type));
            }
            $this->newType = $type;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->newType ?? static::$mimeTypes[$this->mimeType];
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType(): string
    {
        return $this->newType ? array_search($this->newType, static::$mimeTypes, true) : $this->mimeType;
    }

    /**
     * @param int|null $width
     *
     * @return static
     */
    public function setWidth(?int $width = null)
    {
        if (null !== $width) {
            $this->checkResolution($width, 'width');
        }

        $this->newWidth = $width;

        return $this;
    }

    /**
     * @param int $width
     *
     * @return static
     */
    public function setMaxWidth(int $width)
    {
        $this->checkResolution($width, 'width');

        if ($width < $this->getWidth()) {
            $this->newHeight = $width / ($this->getWidth() / $this->getHeight());
            $this->newWidth = $width;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth(): int
    {
        if (null !== $this->newWidth) {
            return $this->newWidth;
        }

        if (null !== $this->newHeight) {
            return $this->newHeight * ($this->width / $this->height);
        }

        return $this->width;
    }

    /**
     * @param int|null $height
     *
     * @return static
     */
    public function setHeight(?int $height = null)
    {
        if (null !== $height) {
            $this->checkResolution($height, 'height');
        }

        $this->newHeight = $height;

        return $this;
    }

    /**
     * @param int $height
     *
     * @return static
     */
    public function setMaxHeight(int $height)
    {
        $this->checkResolution($height, 'height');

        if ($height < $this->getHeight()) {
            $this->newWidth = $height * ($this->getWidth() / $this->getHeight());
            $this->newHeight = $height;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight(): int
    {
        if (null !== $this->newHeight) {
            return $this->newHeight;
        }

        if (null !== $this->newWidth) {
            return $this->newWidth / ($this->width / $this->height);
        }

        return $this->height;
    }

    /**
     * @param int|null $quality
     *
     * @return static
     */
    public function setQuality(?int $quality = null)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int
    {
        if ($this->isChanged()) {
            $this->resize()->close();
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getMd5(bool $rawOutput = false): string
    {
        if ($this->isChanged()) {
            $this->resize()->close();
        }

        return parent::getMd5($rawOutput);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if ($this->isChanged()) {
            $this->resize()->close();
        }

        return parent::getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getStream(): StreamInterface
    {
        if ($this->isChanged()) {
            return $this->resize();
        }

        return parent::getStream();
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        if (null === $this->file) {
            return imagecreatefromstring($this->data);
        }

        $function = 'imagecreatefrom'.static::$mimeTypes[$this->mimeType];

        return $function($this->file);
    }

    /**
     * @see http://www.phash.org/
     * @see https://habr.com/ru/post/120562/
     * @see https://github.com/jenssegers/imagehash/blob/master/src/Implementations/PerceptualHash.php
     *
     * @return Hash
     */
    public function getPerceptualHash(): Hash
    {
        $resource = $this->getResource();
        $size = 8;
        $sampleSize = $size * 4;

        $sample = $image = imagecreatetruecolor($sampleSize, $sampleSize);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $success = imagecopyresampled($sample, $resource, 0, 0, 0, 0, $sampleSize, $sampleSize, $this->width, $this->height);
        if (!$success) {
            throw new \RuntimeException('Can\'t create the sample.');
        }

        $matrix = [];
        $rows = [];
        $row = [];
        for ($y = 0; $y < $sampleSize; ++$y) {
            for ($x = 0; $x < $sampleSize; ++$x) {
                $colorIndex = imagecolorat($sample, $x, $y);
                $color = imagecolorsforindex($sample, $colorIndex);
                $row[$x] = (int) floor(($color['red'] * 0.299) + ($color['green'] * 0.587) + ($color['blue'] * 0.114));
            }
            $rows[$y] = self::calculateDCT($row);
        }

        $col = [];
        for ($x = 0; $x < $sampleSize; ++$x) {
            for ($y = 0; $y < $sampleSize; ++$y) {
                $col[$y] = $rows[$y][$x];
            }
            $matrix[$x] = self::calculateDCT($col);
        }

        // Extract the top 8x8 pixels.
        $pixels = [];
        for ($y = 0; $y < $size; ++$y) {
            for ($x = 0; $x < $size; ++$x) {
                $pixels[] = $matrix[$y][$x];
            }
        }

        $n = \count($pixels) - 1;
        $compare = array_sum(\array_slice($pixels, 1, $n)) / $n;

        $bits = '';
        foreach ($pixels as $pixel) {
            $bits .= ($pixel > $compare) ? '1' : '0';
        }

        return Hash::createFromBits($bits);
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $directory, ?string $name = null)
    {
        if ($this->isChanged()) {
            $this->resize()->close();
        }

        return parent::save($directory, $name);
    }

    /**
     * @return StreamInterface
     */
    protected function resize(): StreamInterface
    {
        $destinationWidth = $this->getWidth();
        $destinationHeight = $this->getHeight();
        $destinationRatio = $destinationWidth / $destinationHeight;
        $sourceWidth = $this->width;
        $sourceHeight = $sourceWidth / $destinationRatio;
        if ($sourceHeight > $this->height) {
            $sourceHeight = $this->height;
            $sourceWidth = $sourceHeight * $destinationRatio;
        }
        $leftOffset = ($this->width - $sourceWidth) / 2;
        $topOffset = ($this->height - $sourceHeight) / 2;
        $type = $this->getType();

        $image = imagecreatetruecolor($destinationWidth, $destinationHeight);
        $color = imagecolorallocate($image, ...static::$background);
        imagefill($image, 0, 0, $color);
        if (self::TYPE_PNG === $type || self::TYPE_WEBP === $type) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        $result = imagecopyresampled(
            $image,
            $this->getResource(),
            0,
            0,
            $leftOffset,
            $topOffset,
            $destinationWidth,
            $destinationHeight,
            $sourceWidth,
            $sourceHeight
        );
        if (!$result) {
            throw new \RuntimeException('Unable to resize image.');
        }

        $saveFunction = 'image'.$type;
        if (self::TYPE_JPEG === $type) {
            imageinterlace($image, static::$interlace);
        }

        $resource = fopen('php://temp', 'r+');
        if (null !== $this->quality && self::TYPE_GIF !== $type) {
            $saveFunction($image, $resource, $this->quality);
        } else {
            $saveFunction($image, $resource);
        }

        imagedestroy($image);

        $this->mimeType = array_search($type, static::$mimeTypes, true);
        $this->width = $destinationWidth;
        $this->height = $destinationHeight;
        $this->newType = null;
        $this->quality = null;
        $this->newWidth = null;
        $this->newHeight = null;
        $this->file = null;

        $stream = stream_for($resource);
        $this->data = (string) $stream;
        $this->size = $stream->getSize();
        $this->hash = Hash::createFromMd5($this->data);

        return $stream;
    }

    /**
     * @return bool
     */
    protected function isChanged(): bool
    {
        return null !== $this->newType
            || null !== $this->newWidth
            || null !== $this->newHeight
            || null !== $this->quality;
    }

    /**
     * @param int    $value
     * @param string $type
     */
    protected function checkResolution(int $value, string $type): void
    {
        if ($value < 1) {
            throw new \InvalidArgumentException(ucfirst($type).' must be 1 px or more.');
        }
        if ($value > static::$options['max_'.$type]) {
            throw new \InvalidArgumentException(sprintf('Max %s is %d px.', $type, static::$options['max_'.$type]));
        }
    }

    /**
     * Perform a 1 dimension Discrete Cosine Transformation.
     *
     * @param array $matrix
     *
     * @return array
     */
    protected static function calculateDCT(array $matrix): array
    {
        $transformed = [];
        $size = \count($matrix);
        for ($i = 0; $i < $size; ++$i) {
            $sum = 0;
            for ($j = 0; $j < $size; ++$j) {
                $sum += $matrix[$j] * cos($i * M_PI * ($j + 0.5) / $size);
            }
            $sum *= sqrt(2 / $size);
            if (0 === $i) {
                $sum *= 1 / sqrt(2);
            }
            $transformed[$i] = $sum;
        }

        return $transformed;
    }
}
