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
class DateTime extends \DateTime implements \JsonSerializable
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
        $date = new static();

        return $date->setTimestamp($timestamp);
    }

    /**
     * @param \DateTimeInterface|string $day
     *
     * @return static
     */
    public static function createStartOfHour($day = 'now'): self
    {
        if (!$day instanceof \DateTimeInterface) {
            $day = new static($day);
            $day->setTime($day->format('H'), 0, 0, 0);
        } else {
            $day = new static($day->format('Y-m-d H:00:00.0'), $day->getTimezone());
        }

        return $day;
    }

    /**
     * @param \DateTimeInterface|string $day
     *
     * @return static
     */
    public static function createEndOfHour($day = 'now'): self
    {
        if (!$day instanceof \DateTimeInterface) {
            $day = new static($day);
            $day->setTime($day->format('H'), 59, 59, 999999);
        } else {
            $day = new static($day->format('Y-m-d H:59:59.999999'), $day->getTimezone());
        }

        return $day;
    }

    /**
     * @param \DateTimeInterface|string $day
     *
     * @return static
     */
    public static function createStartOfDay($day = 'now'): self
    {
        if (!$day instanceof \DateTimeInterface) {
            $day = new static($day);
            $day->setTime(0, 0, 0, 0);
        } else {
            $day = new static($day->format('Y-m-d 00:00:00.0'), $day->getTimezone());
        }

        return $day;
    }

    /**
     * @param \DateTimeInterface|string $day
     *
     * @return static
     */
    public static function createEndOfDay($day = 'now'): self
    {
        if (!$day instanceof \DateTimeInterface) {
            $day = new static($day);
            $day->setTime(23, 59, 59, 999999);
        } else {
            $day = new static($day->format('Y-m-d 23:59:59.999999'), $day->getTimezone());
        }

        return $day;
    }

    /**
     * {@inheritdoc}
     *
     * @param \DateTimeInterface|string $datetime2
     */
    public function diff($datetime2 = 'now', $absolute = false)
    {
        if (!$datetime2 instanceof \DateTimeInterface) {
            $datetime2 = new static($datetime2);
        }

        return parent::diff($datetime2);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->format(self::W3C);
    }
}
