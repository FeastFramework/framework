<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Feast;

use DateTime;
use DateTimeZone;
use Exception;
use Feast\Exception\InvalidDateException;

/**
 * Manages Dates from different formats to different formats
 * This class is an alternative to \DateTime
 */
class Date
{

    public const FORMAT_YMD_WITH_SLASHES = 'Y/m/d';
    public const FORMAT_YMD_HMS_WITH_SLASHES = 'Y/m/d H:i:s';
    public const FORMAT_YMD_HM_WITH_SLASHES = 'Y/m/d H:i';
    public const FORMAT_YMD_WITH_DASHES = 'Y-m-d';
    public const FORMAT_YMD_HMS_WITH_DASHES = 'Y-m-d H:i:s';
    public const FORMAT_YMD_HM_WITH_DASHES = 'Y-m-d H:i';
    public const FORMAT_MDY_WITH_SLASHES = 'm/d/Y';
    public const FORMAT_MDY_HMS_WITH_SLASHES = 'm/d/Y H:i:s';
    public const FORMAT_MDY_HM_WITH_SLASHES = 'm/d/Y H:i';
    public const FORMAT_MDY_WITH_DASHES = 'm-d-Y';
    public const FORMAT_MDY_HMS_WITH_DASHES = 'm-d-Y H:i:s';
    public const FORMAT_MDY_HM_WITH_DASHES = 'm-d-y H:i';
    public const FORMAT_MDY_TEXT_FORMAT_WITHOUT_SUFFIX = 'F j, Y';
    public const FORMAT_MDY_TEXT_FORMAT_WITH_SUFFIX = 'F jS, Y';
    public const FORMAT_HMS = 'H:i:s';
    public const FORMAT_HM = 'H:i';
    public const FORMAT_UNIXTIMESTAMP = 'U';
    public const ATOM = "Y-m-d\TH:i:sP";
    public const COOKIE = "l, d-M-Y H:i:s T";
    public const ISO8601 = "Y-m-d\TH:i:sO";
    public const RFC822 = "D, d M y H:i:s O";
    public const RFC850 = "l, d-M-y H:i:s T";
    public const RFC1036 = "D, d M y H:i:s O";
    public const RFC1123 = "D, d M Y H:i:s O";
    public const RFC2822 = "D, d M Y H:i:s O";
    public const RFC3339 = "Y-m-d\TH:i:sP";
    public const RFC3339_EXTENDED = "Y-m-d\TH:i:s.vP";
    public const RSS = "D, d M Y H:i:s O";
    public const W3C = "Y-m-d\TH:i:sP";

    private int $timestamp;
    protected ?string $timezone = null;
    protected string $serverTimezone;

    /**
     * Create Date object from Unix Timestamp.
     *
     * @param int $timestamp
     * @param string|null $timezone
     * @return Date
     * @throws InvalidDateException
     */
    public static function createFromTimestamp(int $timestamp, ?string $timezone = null): self
    {
        $serverTimezone = date_default_timezone_get();
        if ($timezone) {
            date_default_timezone_set($timezone);
        }

        $params = [
            date('Y', $timestamp), date('m', $timestamp), date('d', $timestamp), date('H', $timestamp),
            date('i', $timestamp),
            date('s', $timestamp),
            $timezone
        ];
        date_default_timezone_set($serverTimezone);
        
        return new self(...$params);
    }

    /**
     * Create Date object from now.
     *
     * @param string|null $timezone
     * @return self
     * @throws InvalidDateException
     */
    public static function createFromNow(?string $timezone = null): self
    {
        return self::createFromTimestamp(time(), $timezone);
    }

    /**
     * Create Date object from date string.
     *
     * Example: 2020-01-01; 2021-03-26 14:34:45
     *
     * @param string $date
     * @param string|null $timezone
     * @return self
     * @throws InvalidDateException
     */
    public static function createFromString(string $date, ?string $timezone = null): self
    {
        // Timestamp based
        $serverTimezone = date_default_timezone_get();
        if ($timezone) {
            date_default_timezone_set($timezone);
        }

        if (str_contains($date, '-') && str_contains($date, '/')) {
            throw new InvalidDateException('Invalid date string - ' . $date);
        }
        if (strtotime($date) === false) {
            throw new InvalidDateException('Invalid date string - ' . $date);
        }
        $date = date('Y-m-d H:i:s', strtotime($date . ' ' . ($timezone ?: '')));

        $params = [
            substr($date, 0, 4), substr($date, 5, 2), substr($date, 8, 2), substr($date, 11, 2),
            substr($date, 14, 2), substr($date, 17, 2), $timezone];
        
        date_default_timezone_set($serverTimezone);
        
        return new self(...$params);
    }

    /**
     * Create a Date object from a specific format string.
     *
     * This function uses PHP's built in DateTime as an intermediary
     * and follows all the rules of that class' constructor.
     *
     * @param string $format
     * @param string $dateString
     * @param string|null $timezone
     * @return self
     * @throws InvalidDateException
     */
    public static function createFromFormat(string $format, string $dateString, ?string $timezone = null): self
    {
        $timezoneData = isset($timezone) ? new DateTimeZone($timezone) : null;
        $date = DateTime::createFromFormat($format, $dateString, $timezoneData);
        return self::createFromTimestamp($date->getTimestamp(), $timezone);
    }

    /**
     * @throws InvalidDateException
     */
    public function __construct(
        string|int $year,
        string|int $month,
        string|int $day,
        string|int $hour = 0,
        string|int $minute = 0,
        string|int $second = 0,
        string $timezone = null
    ) {
        if (!ctype_digit((string)$year) ||
            !ctype_digit((string)$month) ||
            !ctype_digit((string)$day) ||
            !ctype_digit((string)$hour) ||
            !ctype_digit((string)$minute) ||
            !ctype_digit((string)$second)) {
            throw new InvalidDateException('Invalid date');
        }
        $year = (int)$year;
        $month = (int)$month;
        $day = (int)$day;
        $hour = (int)$hour;
        $minute = (int)$minute;
        $second = (int)$second;
        $this->throwIfInvalidDate($hour, $minute, $second, $month, $day, $year);

        $this->serverTimezone = date_default_timezone_get();
        if ($timezone) {
            $this->timezone = $timezone;
            date_default_timezone_set($timezone);
        }
        $this->timestamp = mktime($hour, $minute, $second, $month, $day, $year);
        date_default_timezone_set($this->serverTimezone);
    }

    /**
     * Set timezone for date object.
     *
     * @param string $timezone
     */
    public function setTimezone(string $timezone): void
    {
        $this->timezone = $timezone;
    }

    /**
     * Get Unix Timestamp.
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Get year.
     *
     * @return string
     */
    public function getYear(): string
    {
        return $this->getFormattedDate('Y');
    }

    /**
     * Get Month (2 digit numeric format).
     *
     * @return string
     */
    public function getMonth(): string
    {
        return $this->getFormattedDate('m');
    }

    /**
     * Get day of year.
     *
     * @return string
     */
    public function getDay(): string
    {
        return $this->getFormattedDate('d');
    }

    /**
     * Get hour.
     *
     * @return string
     */
    public function getHour(): string
    {
        return $this->getFormattedDate('H');
    }

    /**
     * Get minute.
     *
     * @return string
     */
    public function getMinute(): string
    {
        return $this->getFormattedDate('i');
    }

    /**
     * Get second.
     *
     * @return string
     */
    public function getSecond(): string
    {
        return $this->getFormattedDate('s');
    }

    /**
     * Get day of the week (0 = Sunday, 6 = Saturday).
     *
     * @return string
     */
    public function getDayOfWeek(): string
    {
        return $this->getFormattedDate('w');
    }

    /**
     * Get day of the year (0-365).
     *
     * @return string
     */
    public function getDayOfYear(): string
    {
        return $this->getFormattedDate('z');
    }

    public function isDaylightSavingsTime(): bool
    {
        return $this->getFormattedDate('I') === '1';
    }

    public function isLeapYear(): bool
    {
        return $this->getFormattedDate('L') === '1';
    }

    /**
     * Set the year.
     *
     * @param int $year
     * @return self
     */
    public function setYear(int $year): self
    {
        $this->timestamp = mktime(
            (int)$this->getHour(),
            (int)$this->getMinute(),
            (int)$this->getSecond(),
            (int)$this->getMonth(),
            (int)$this->getDay(),
            $year
        );

        return $this;
    }

    /**
     * Set the month.
     *
     * @param int $month
     * @return self
     */
    public function setMonth(int $month): self
    {
        $this->timestamp = mktime(
            (int)$this->getHour(),
            (int)$this->getMinute(),
            (int)$this->getSecond(),
            $month,
            (int)$this->getDay(),
            (int)$this->getYear()
        );

        return $this;
    }

    /**
     * Set the day.
     *
     * @param int $day
     * @return self
     */
    public function setDay(int $day): self
    {
        $this->timestamp = mktime(
            (int)$this->getHour(),
            (int)$this->getMinute(),
            (int)$this->getSecond(),
            (int)$this->getMonth(),
            $day,
            (int)$this->getYear()
        );

        return $this;
    }

    /**
     * Set the hour.
     *
     * @param int $hour
     * @return self
     */
    public function setHour(int $hour): self
    {
        $this->timestamp = mktime(
            $hour,
            (int)$this->getMinute(),
            (int)$this->getSecond(),
            (int)$this->getMonth(),
            (int)$this->getDay(),
            (int)$this->getYear()
        );

        return $this;
    }

    /**
     * Set the minute.
     *
     * @param int $minute
     * @return self
     */
    public function setMinute(int $minute): self
    {
        $this->timestamp = mktime(
            (int)$this->getHour(),
            $minute,
            (int)$this->getSecond(),
            (int)$this->getMonth(),
            (int)$this->getDay(),
            (int)$this->getYear()
        );

        return $this;
    }

    /**
     * Set the second.
     *
     * @param int $second
     * @return self
     */
    public function setSecond(int $second): self
    {
        $this->timestamp = mktime(
            (int)$this->getHour(),
            (int)$this->getMinute(),
            $second,
            (int)$this->getMonth(),
            (int)$this->getDay(),
            (int)$this->getYear()
        );

        return $this;
    }

    /**
     * Get date string for the chosen format.
     *
     * @param string $format
     * @return string
     */
    public function getFormattedDate(string $format = 'Y-m-d H:i:s'): string
    {
        if ($this->timezone !== null) {
            date_default_timezone_set($this->timezone);
        }
        $return = date($format, $this->timestamp);
        date_default_timezone_set($this->serverTimezone);
        return $return;
    }

    public function __toString(): string
    {
        return $this->getFormattedDate();
    }

    /**
     * Modify date by a text based format.
     *
     * @param string $amount
     * @return $this
     * @throws InvalidDateException
     */
    public function modify(string $amount): Date
    {
        $modification = strtotime($amount, $this->timestamp);
        if ($modification === false) {
            throw new InvalidDateException('Invalid date modification');
        }
        $this->timestamp = $modification;

        return $this;
    }

    /**
     * Get as PHP DateTime object.
     *
     * @return DateTime
     * @throws Exception
     */
    public function getAsDateTime(): DateTime
    {
        $timezone = $this->timezone ? new DateTimeZone($this->timezone) : null;
        return new DateTime($this->getFormattedDate(self::ISO8601), $timezone);
    }

    /**
     * Check if this Date is greater than or equal to a passed in Date.
     *
     * @param Date $date
     * @return bool
     */
    public function greaterThanEqual(Date $date): bool
    {
        return $this->timestamp >= $date->timestamp;
    }

    /**
     * Check if this Date is greater than to a passed in Date.
     *
     * @param Date $date
     * @return bool
     */
    public function greaterThan(Date $date): bool
    {
        return $this->timestamp > $date->timestamp;
    }

    /**
     * Check if this Date is less than or equal to a passed in Date.
     *
     * @param Date $date
     * @return bool
     */
    public function lessThanEqual(Date $date): bool
    {
        return $this->timestamp <= $date->timestamp;
    }

    /**
     * Check if this Date is less than to a passed in Date.
     *
     * @param Date $date
     * @return bool
     */
    public function lessThan(Date $date): bool
    {
        return $this->timestamp < $date->timestamp;
    }

    /**
     * @throws InvalidDateException
     */
    protected function throwIfInvalidDate(int $hour, int $minute, int $second, int $month, int $day, int $year): void
    {
        if (checkdate($month, $day, $year) === false) {
            throw new InvalidDateException('Invalid date');
        }

        if ($hour > 23 || $hour < 0) {
            throw new InvalidDateException('Invalid date');
        }
        if ($minute > 59 || $minute < 0) {
            throw new InvalidDateException('Invalid date');
        }
        if ($second > 59 || $second < 0) {
            throw new InvalidDateException('Invalid date');
        }
    }
}
