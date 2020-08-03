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
 * Class DateTime.
 */
class DateTime extends \DateTimeImmutable implements \JsonSerializable
{
    const MINUTE = 60;
    const HOUR = 60 * self::MINUTE;
    const DAY = 24 * self::HOUR;
    const WEEK = 7 * self::DAY;
    const MONTH = 30 * self::DAY;
    const YEAR = 365 * self::DAY;

    /**
     * Return Date in ISO8601 format.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format(self::W3C);
    }

    /**
     * @param int $timestamp
     *
     * @return static
     */
    public static function createFromTimestamp(int $timestamp): self
    {
        return (new static())->setTimestamp($timestamp);
    }

    /**
     * @param \DateTimeInterface|string $date
     *
     * @return static
     */
    public static function createStartOfHour($date = 'now'): self
    {
        if (!$date instanceof \DateTimeInterface) {
            $date = new static($date);
            $date = $date->setTime($date->format('H'), 0, 0, 0);
        } else {
            $date = new static($date->format('Y-m-d H:00:00.0'), $date->getTimezone());
        }

        return $date;
    }

    /**
     * @param \DateTimeInterface|string $date
     *
     * @return static
     */
    public static function createEndOfHour($date = 'now'): self
    {
        if (!$date instanceof \DateTimeInterface) {
            $date = new static($date);
            $date = $date->setTime($date->format('H'), 59, 59, 999999);
        } else {
            $date = new static($date->format('Y-m-d H:59:59.999999'), $date->getTimezone());
        }

        return $date;
    }

    /**
     * @param \DateTimeInterface|string $date
     *
     * @return static
     */
    public static function createStartOfDay($date = 'now'): self
    {
        if (!$date instanceof \DateTimeInterface) {
            $date = new static($date);
            $date = $date->setTime(0, 0, 0, 0);
        } else {
            $date = new static($date->format('Y-m-d 00:00:00.0'), $date->getTimezone());
        }

        return $date;
    }

    /**
     * @param \DateTimeInterface|string $date
     *
     * @return static
     */
    public static function createEndOfDay($date = 'now'): self
    {
        if (!$date instanceof \DateTimeInterface) {
            $date = new static($date);
            $date = $date->setTime(23, 59, 59, 999999);
        } else {
            $date = new static($date->format('Y-m-d 23:59:59.999999'), $date->getTimezone());
        }

        return $date;
    }

    /**
     * {@inheritdoc}
     *
     * @param \DateTimeInterface|string $datetime2
     */
    public function diff($datetime2 = 'now', $absolute = false)
    {
        if (!$datetime2 instanceof \DateTimeInterface) {
            $datetime2 = new static($datetime2, $this->getTimezone());
        }

        return parent::diff($datetime2, $absolute);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->format(self::W3C);
    }

    /**
     * @return static
     */
    public function getStartOfHour(): self
    {
        return $this->setTime($this->format('H'), 0, 0, 0);
    }

    /**
     * @return static
     */
    public function getEndOfHour(): self
    {
        return $this->setTime($this->format('H'), 59, 59, 999999);
    }

    /**
     * @return static
     */
    public function getStartOfDay(): self
    {
        return $this->setTime(0, 0, 0, 0);
    }

    /**
     * @return static
     */
    public function getEndOfDay(): self
    {
        return $this->setTime(23, 59, 59, 999999);
    }
}
