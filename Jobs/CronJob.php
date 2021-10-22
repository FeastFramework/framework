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

namespace Feast\Jobs;

use Feast\Date;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\JobInterface;

abstract class CronJob implements JobInterface
{
    protected string $checkEnv = 'production';
    protected string $cronString = '* * * * *';
    protected bool $allowOverlap = true;
    protected int $overlapTime = 1440;
    protected ?string $betweenStart = null;
    protected ?string $betweenEnd = null;
    protected ?string $unlessBetweenStart = null;
    protected ?string $unlessBetweenEnd = null;
    protected bool $criteria = true;
    protected bool $runInMaintenanceMode = false;
    protected ?string $timezone = null;
    protected bool $runInBackground = false;

    /**
     * Get the overlap prevention timeout.
     *
     * @return int|null
     */
    public function getOverlapTime(): ?int
    {
        return !$this->allowOverlap ? $this->overlapTime : null;
    }

    /**
     * Get the cron string for the job.
     *
     * @return string
     */
    public function getCronString(): string
    {
        return $this->cronString;
    }

    /**
     * Check if able to run in background (on supported OSes).
     *
     * @return bool
     */
    public function isBackgroundable(): bool
    {
        return $this->runInBackground;
    }

    /**
     * Set to run in background (on supported OSes).
     *
     * @return $this
     */
    public function runInBackground(): static
    {
        $this->runInBackground = true;
        return $this;
    }

    /**
     * Start job run.
     *
     * @param Date $now
     */
    public function startRun(Date $now): void
    {
        if ($this->allowOverlap === false) {
            file_put_contents(
                APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cronrunning' . DIRECTORY_SEPARATOR . md5(
                    self::class
                ) . '.txt',
                (string)$now->getTimestamp()
            );
        }
    }

    /**
     * Stop job run.
     *
     * @param Date $now
     */
    public function stopRun(Date $now): void
    {
        if ($this->allowOverlap === false) {
            $file = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cronrunning' . DIRECTORY_SEPARATOR . md5(
                    self::class
                ) . '.txt';
            if (file_exists($file) &&
                str_starts_with(
                    file_get_contents($file),
                    (string)$now->getTimestamp()
                )) {
                unlink($file);
            }
        }
    }

    /**
     * Set environment to run in.
     *
     * @param string $env
     * @return $this
     */
    public function environment(string $env): static
    {
        $this->checkEnv = $env;
        return $this;
    }

    /**
     * Set disallow overlap with timeout. Exceeding this timeout will cause the job to be allowed to run.
     *
     * @param int $minutes
     * @return $this
     */
    public function withoutOverlapping(int $minutes = 1440): static
    {
        $this->allowOverlap = false;
        $this->overlapTime = $minutes;

        return $this;
    }

    /**
     * Set the cron string.
     *
     * @param string $cronString
     * @return $this
     */
    public function cron(string $cronString): static
    {
        $this->cronString = $cronString;
        return $this;
    }

    /**
     * Mark job to run every minute.
     *
     * @return $this
     */
    public function everyMinute(): static
    {
        [, $hour, $dayOfMonth, $month, $dayOfWeek] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', ['*', $hour, $dayOfMonth, $month, $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run every odd minute (1,3,5 etc).
     *
     * @return $this
     */
    public function everyOddMinute(): static
    {
        [, $hour, $dayOfMonth, $month, $dayOfWeek] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', ['1-59/2', $hour, $dayOfMonth, $month, $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run every two minutes.
     *
     * @return $this
     */
    public function everyTwoMinutes(): static
    {
        return $this->everyXDivisibleMinutes(2);
    }

    /**
     * Mark job to run every three minutes.
     *
     * @return $this
     */
    public function everyThreeMinutes(): static
    {
        return $this->everyXDivisibleMinutes(3);
    }

    /**
     * Mark job to run every four minutes.
     *
     * @return $this
     */
    public function everyFourMinutes(): static
    {
        return $this->everyXDivisibleMinutes(4);
    }

    /**
     * Mark job to run every five minutes.
     *
     * @return $this
     */
    public function everyFiveMinutes(): static
    {
        return $this->everyXDivisibleMinutes(5);
    }

    /**
     * Mark job to run every ten minutes.
     *
     * @return $this
     */
    public function everyTenMinutes(): static
    {
        return $this->everyXDivisibleMinutes(10);
    }

    /**
     * Mark job to run every fifteen minutes.
     *
     * @return $this
     */
    public function everyFifteenMinutes(): static
    {
        return $this->everyXDivisibleMinutes(15);
    }

    /**
     * Mark job to run every twenty minutes.
     *
     * @return $this
     */
    public function everyTwentyMinutes(): static
    {
        return $this->everyXDivisibleMinutes(20);
    }

    /**
     * Mark job to run every thirty minutes.
     *
     * @return $this
     */
    public function everyThirtyMinutes(): static
    {
        return $this->everyXDivisibleMinutes(30);
    }

    /**
     * Mark job to run every minute divisible by X minutes.
     *
     * @param int $x
     * @return $this
     */
    protected function everyXDivisibleMinutes(int $x): static
    {
        [, $hour, $dayOfMonth, $month, $dayOfWeek] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', ['*/' . (string)$x, $hour, $dayOfMonth, $month, $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run every hour. Defaults to on the hour.
     *
     * @return $this
     */
    public function hourly(): static
    {
        [$minute, , $dayOfMonth, $month, $dayOfWeek] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $this->cronString = implode(' ', [$minute, '*', $dayOfMonth, $month, $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run every hour at a specific minute.
     *
     * @param int $minuteNew
     * @return $this
     */
    public function hourlyAt(int $minuteNew): static
    {
        [, , $dayOfMonth, $month, $dayOfWeek] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', [$minuteNew, '*', $dayOfMonth, $month, $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run every odd hour (1,3,5 etc).
     *
     * @return $this
     */
    public function hourlyOnOddHours(): static
    {
        [$minute, , $dayOfMonth, $month, $dayOfWeek] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $this->cronString = implode(' ', [$minute, '1-23/2', $dayOfMonth, $month, $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run every two hours.
     *
     * @return $this
     */
    public function everyTwoHours(): static
    {
        return $this->everyXDivisibleHours(2);
    }

    /**
     * Mark job to run every three hours.
     *
     * @return $this
     */
    public function everyThreeHours(): static
    {
        return $this->everyXDivisibleHours(3);
    }

    /**
     * Mark job to run every four hours.
     *
     * @return $this
     */
    public function everyFourHours(): static
    {
        return $this->everyXDivisibleHours(4);
    }

    /**
     * Mark job to run every six hours.
     *
     * @return $this
     */
    public function everySixHours(): static
    {
        return $this->everyXDivisibleHours(6);
    }

    /**
     * Mark job to run every eight hours.
     *
     * @return $this
     */
    public function everyEightHours(): static
    {
        return $this->everyXDivisibleHours(8);
    }

    /**
     * Mark job to run every twelve hours.
     *
     * @return $this
     */
    public function everyTwelveHours(): static
    {
        return $this->everyXDivisibleHours(12);
    }

    /**
     * Mark job to run every x divisible hours.
     *
     * @param int $x
     * @return $this
     */
    protected function everyXDivisibleHours(int $x): static
    {
        [$minute, , $dayOfMonth, $month, $dayOfWeek] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $this->cronString = implode(' ', [$minute, '*/' . $x, $dayOfMonth, $month, $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run once daily.
     *
     * @return $this
     */
    public function daily(): static
    {
        [$minute, $hour, $dayOfMonth, $month, $dayOfWeek] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $hour = $hour === '*' ? '0' : $hour;
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run once daily at a specific time (in 24 hour time format).
     *
     * @param string $time
     * @return $this
     */
    public function dailyAt(string $time): static
    {
        [, , $dayOfMonth, $month, $dayOfWeek] = explode(' ', $this->cronString);

        [$hour, $minute] = explode(':', $time);
        $this->cronString = implode(' ', [(string)(int)$minute, (string)(int)$hour, $dayOfMonth, $month, $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run twice daily at the set hours.
     *
     * @param int $hour1
     * @param int $hour2
     * @return $this
     */
    public function twiceDaily(int $hour1, int $hour2): static
    {
        [$minute, , $dayOfMonth, $month, $dayOfWeek] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $hour = implode(',', [$hour1, $hour2]);
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run once weekly on Sunday.
     *
     * @return $this
     */
    public function weekly(): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $hour = $hour === '*' ? '0' : $hour;
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, '0']);
        return $this;
    }

    /**
     * Mark job to run once weekly on a specific day at an optional specific time.
     *
     * @param int $dayOfWeekNew
     * @param string|null $time
     * @return $this
     */
    public function weeklyOn(int $dayOfWeekNew, ?string $time = null): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $hour = $hour === '*' ? '0' : $hour;
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, $dayOfWeekNew]);
        if ($time !== null) {
            $this->dailyAt($time);
        }
        return $this;
    }

    /**
     * Mark job to run once a month on the first of the month.
     *
     * @return $this
     */
    public function monthly(): static
    {
        [$minute, $hour, , $month, $dayOfWeek] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $hour = $hour === '*' ? '0' : $hour;
        $this->cronString = implode(' ', [$minute, $hour, '1', $month, $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run once a month on a specific day at an optional specific time.
     *
     * @param int $dayOfMonthNew
     * @param string|null $time
     * @return $this
     */
    public function monthlyOn(int $dayOfMonthNew, ?string $time = null): static
    {
        [$minute, $hour, , $month, $dayOfWeek] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $hour = $hour === '*' ? '0' : $hour;
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonthNew, $month, $dayOfWeek]);
        if ($time !== null) {
            $this->dailyAt($time);
        }
        return $this;
    }

    /**
     * Mark job to run twice a month on specific days at an optional specific time.
     *
     * @param int $dayOfMonthOne
     * @param int $dayOfMonthTwo
     * @param string|null $time
     * @return $this
     */
    public function twiceMonthly(int $dayOfMonthOne, int $dayOfMonthTwo, ?string $time = null): static
    {
        [$minute, $hour, , $month, $dayOfWeek] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $hour = $hour === '*' ? '0' : $hour;
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonthOne . ',' . $dayOfMonthTwo, $month, $dayOfWeek]);
        if ($time !== null) {
            $this->dailyAt($time);
        }
        return $this;
    }

    /**
     * Mark job to run once per quarter.
     *
     * Runs on Jan, April, July, and October 1st
     *
     * @return $this
     */
    public function quarterly(): static
    {
        [$minute, $hour, $dayOfMonth, , $dayOfWeek] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $hour = $hour === '*' ? '0' : $hour;
        $dayOfMonth = $dayOfMonth === '*' ? '1' : $dayOfMonth;
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, '1,4,7,10', $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run once a year.
     *
     * @return $this
     */
    public function yearly(): static
    {
        [$minute, $hour, $dayOfMonth, , $dayOfWeek] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $hour = $hour === '*' ? '0' : $hour;
        $dayOfMonth = $dayOfMonth === '*' ? '1' : $dayOfMonth;
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, '1', $dayOfWeek]);
        return $this;
    }

    /**
     * Mark job to run once a year on a specific day at an optional specific time.
     *
     * @param int $dayOfMonthNew
     * @param int $monthNew
     * @param string|null $time
     * @return $this
     */
    public function yearlyOn(int $dayOfMonthNew, int $monthNew, ?string $time = null): static
    {
        [$minute, $hour, , , $dayOfWeek] = explode(' ', $this->cronString);
        $minute = $minute === '*' ? '0' : $minute;
        $hour = $hour === '*' ? '0' : $hour;
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonthNew, $monthNew, $dayOfWeek]);
        if ($time !== null) {
            $this->dailyAt($time);
        }
        return $this;
    }

    /**
     * Mark job to run on weekdays.
     *
     * @return $this
     */
    public function weekdays(): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, '1,2,3,4,5']);
        return $this;
    }

    /**
     * Mark job to run on weekends.
     *
     * @return $this
     */
    public function weekends(): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, '0,6']);
        return $this;
    }

    /**
     * Mark job to run on Sundays.
     *
     * @return $this
     */
    public function sundays(): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, '0']);
        return $this;
    }

    /**
     * Mark job to run on Mondays.
     *
     * @return $this
     */
    public function mondays(): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, '1']);
        return $this;
    }

    /**
     * Mark job to run on Tuesdays.
     *
     * @return $this
     */
    public function tuesdays(): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, '2']);
        return $this;
    }

    /**
     * Mark job to run on Wednesdays.
     *
     * @return $this
     */
    public function wednesdays(): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, '3']);
        return $this;
    }

    /**
     * Mark job to run on Thursday.
     *
     * @return $this
     */
    public function thursdays(): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, '4']);
        return $this;
    }

    /**
     * Mark job to run on Friday.
     *
     * @return $this
     */
    public function fridays(): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, '5']);
        return $this;
    }

    /**
     * Mark job to run on Saturday.
     *
     * @return $this
     */
    public function saturdays(): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, '6']);
        return $this;
    }

    /**
     * Mark job to run on specific days of the week in cron format.
     *
     * 1-6 is Monday through Saturday. 0 or 7 are Sunday.
     *
     * @param array<int> $days
     * @return static
     */
    public function days(array $days): static
    {
        [$minute, $hour, $dayOfMonth, $month,] = explode(' ', $this->cronString);
        $this->cronString = implode(' ', [$minute, $hour, $dayOfMonth, $month, implode(',', $days)]);
        return $this;
    }

    /**
     * Mark job to only run if cron conditions are met AND between the chosen times.
     *
     * @param string $startTime
     * @param string $endTime
     * @return $this
     */
    public function between(string $startTime, string $endTime): static
    {
        $this->betweenStart = str_pad($startTime, 5, ':', STR_PAD_LEFT);
        $this->betweenEnd = str_pad($endTime, 5, ':', STR_PAD_LEFT);
        return $this;
    }

    /**
     * Mark job to only run if cron conditions are met EXCEPT between the chosen times.
     *
     * @param string $startTime
     * @param string $endTime
     * @return $this
     */
    public function unlessBetween(string $startTime, string $endTime): static
    {
        $this->unlessBetweenStart = str_pad($startTime, 5, ':', STR_PAD_LEFT);
        $this->unlessBetweenEnd = str_pad($endTime, 5, ':', STR_PAD_LEFT);
        return $this;
    }

    /**
     * Mark job to run if the contained expression evaluates to true. Evaluated immediately when "when" is called.
     *
     * @param bool $criteria
     * @return $this
     */
    public function when(bool $criteria): static
    {
        $this->criteria = $criteria;
        return $this;
    }

    /**
     * Mark the timezone to be used to determine if the job should run.
     *
     * @param string $timezone
     * @return $this
     */
    public function timezone(string $timezone): static
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Check if job criteria to run is all met.
     *
     * @param Date $now
     * @param ConfigInterface $config
     * @return bool
     */
    public function shouldRun(Date $now, ConfigInterface $config): bool
    {
        if ($this->timezone !== null) {
            $now->setTimezone($this->timezone);
        }
        return $this->criteria && $this->checkMaintenanceMode() &&
            $config->getEnvironmentName() === $this->checkEnv &&
            $this->isRunningOverlapSafe($now) &&
            $this->checkBetween($now) &&
            $this->checkNotBetween($now) &&
            $this->shouldRunCronCheck($now);
    }

    protected function checkMaintenanceMode(): bool
    {
        return $this->runInMaintenanceMode || !file_exists(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'maintenance.txt');
    }

    protected function checkBetween(Date $now): bool
    {
        if ($this->betweenStart === null) {
            return true;
        }
        $start = (int)str_replace(':', '', $this->betweenStart . '00');
        $end = (int)str_replace(':', '', $this->betweenEnd . '59');
        $check = $now->getFormattedDate('His');
        return $check >= $start && $check <= $end;
    }

    protected function checkNotBetween(Date $now): bool
    {
        if ($this->unlessBetweenStart === null) {
            return true;
        }
        $start = (int)str_replace(':', '', $this->unlessBetweenStart . '00');
        $end = (int)str_replace(':', '', $this->unlessBetweenEnd . '59');
        $check = $now->getFormattedDate('His');
        return $check < $start || $check > $end;
    }

    protected function isRunningOverlapSafe(Date $now): bool
    {
        if ($this->allowOverlap) {
            return true;
        }
        $file = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cronrunning' . DIRECTORY_SEPARATOR . md5(
                self::class
            ) . '.txt';
        if (file_exists($file)) {
            $timestamp = (int)file_get_contents($file);
            $ranTime = Date::createFromTimestamp($timestamp);
            $ranTime->modify('+' . $this->overlapTime . ' minutes');
            if ($ranTime->greaterThan($now)) {
                return false;
            }
        }
        return true;
    }

    protected function checkIfHourly(Date $now): bool
    {
        if ($this->cronString === '@hourly' || $this->cronString === '0 * * * *') {
            return $now->getMinute() === '00';
        }
        return false;
    }

    protected function checkIfDaily(Date $now): bool
    {
        if ($this->cronString === '@daily' || $this->cronString === '@midnight' || $this->cronString === '0 0 * * *') {
            return $now->getMinute() === '00' && $now->getHour() === '00';
        }
        return false;
    }

    protected function checkIfWeekly(Date $now): bool
    {
        if ($this->cronString === '@weekly' || $this->cronString === '0 0 * * 0' || $this->cronString === '0 0 * * 7') {
            return $now->getMinute() === '00' && $now->getHour() === '00' && $now->getFormattedDate('w') === '0';
        }
        return false;
    }

    protected function checkIfMonthly(Date $now): bool
    {
        if ($this->cronString === '@monthly' || $this->cronString === '0 0 1 * *') {
            return $now->getMinute() === '00' && $now->getHour() === '00' && $now->getDay() === '01';
        }
        return false;
    }

    protected function checkIfYearly(Date $now): bool
    {
        if ($this->cronString === '@yearly' || $this->cronString === '@annually' || $this->cronString === '0 0 1 1 *') {
            return $now->getMinute() === '00' &&
                $now->getHour() === '00' &&
                $now->getDay() === '01' &&
                $now->getMonth() === '01';
        }
        return false;
    }

    public function evenInMaintenanceMode(): static
    {
        $this->runInMaintenanceMode = true;
        return $this;
    }

    protected function checkCronString(Date $now): bool
    {
        if (str_starts_with($this->cronString, '@')) {
            return false;
        }

        [$minute, $hour, $dayOfMonth, $month, $dayOfWeek] = explode(' ', $this->cronString);
        return $this->checkPiece($minute, (int)$now->getMinute()) &&
            $this->checkPiece($hour, (int)$now->getHour()) &&
            $this->checkPiece($dayOfMonth, (int)$now->getDay()) &&
            $this->checkPiece($month, (int)$now->getMonth()) &&
            ($this->checkPiece($dayOfWeek, (int)$now->getFormattedDate('w')) || $this->checkPiece(
                    $dayOfWeek,
                    (int)$now->getFormattedDate(
                        'N'
                    )
                ));
    }

    protected function checkPiece(string $field, int $value): bool
    {
        if ($field === '*') {
            return true;
        }

        $pieces = explode(',', $field);
        $ruleMet = false;
        foreach ($pieces as $piece) {
            $ruleMet = $ruleMet || $this->checkSubPiece($piece, $value);
        }
        return $ruleMet;
    }

    protected function checkSubPiece(string $field, int $value): bool
    {
        $divisor = null;
        $rulesPassed = true;
        $start = null;
        if (str_contains($field, '/')) {
            [$field, $divisor] = explode('/', $field, 2);
        }
        if ($divisor !== null && str_contains($field, '-') === false) {
            $start = $field === '*' ? 0 : $field;
            $end = 59;
            $rulesPassed = $value >= $start && $value <= $end;
        } elseif (str_contains($field, '-')) {
            [$start, $end] = explode('-', $field);
            $rulesPassed = $value >= $start && $value <= $end;
        } elseif ($field !== '*') {
            $rulesPassed = (int)trim($field) === $value;
        }
        if ($divisor !== null) {
            $divisor = (int)$divisor;
            if ($divisor === 0) {
                $rulesPassed = $rulesPassed && $value === 0;
            } elseif ($start !== $value) {
                $rulesPassed = $rulesPassed && ($value - $start) % $divisor === 0;
            }
        }
        return $rulesPassed;
    }

    protected function shouldRunCronCheck(Date $now): bool
    {
        return $this->checkIfHourly($now) ||
            $this->checkIfDaily($now) ||
            $this->checkIfMonthly($now) ||
            $this->checkIfWeekly($now) ||
            $this->checkIfYearly($now) ||
            $this->checkCronString($now);
    }

}
