<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

use Feast\Date;
use Feast\Exception\InvalidDateException;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DateTest extends TestCase
{

    public function testMDY_His_With_Dashes(): void
    {
        $date = Date::createFromString('2014-01-15 23:55:55');
        $this->assertEquals('2014-01-15', $date->getFormattedDate(Date::FORMAT_YMD_WITH_DASHES));
    }

    public function testMDY_With_Dashes(): void
    {
        $date = Date::createFromString('2014-01-15');
        $this->assertEquals('2014-01-15', $date->getFormattedDate(Date::FORMAT_YMD_WITH_DASHES));
    }

    public function testMDY_His_With_Slashes(): void
    {
        $date = Date::createFromString('2014/01/15 23:55:55');
        $this->assertEquals('2014/01/15', $date->getFormattedDate(Date::FORMAT_YMD_WITH_SLASHES));
    }

    public function testMDY_With_Slashes(): void
    {
        $date = Date::createFromString('2014/01/15');
        $this->assertEquals('2014/01/15', $date->getFormattedDate(Date::FORMAT_YMD_WITH_SLASHES));
    }

    public function testMDY_Text_With_Suffix(): void
    {
        $date = Date::createFromString('2014/01/15 01:34:55');
        $this->assertEquals('January 15th, 2014', $date->getFormattedDate(Date::FORMAT_MDY_TEXT_FORMAT_WITH_SUFFIX));
    }

    public function testMDY_Text_Without_Suffix(): void
    {
        $date = Date::createFromString('2014/01/15 01:34:55');
        $this->assertEquals('January 15, 2014', $date->getFormattedDate(Date::FORMAT_MDY_TEXT_FORMAT_WITHOUT_SUFFIX));
    }

    public function testMDY_Leapyear(): void
    {
        $date = Date::createFromString('2012/02/29');
        $this->assertEquals('2012/02/29', $date->getFormattedDate(Date::FORMAT_YMD_WITH_SLASHES));
    }

    public function testFromPM(): void
    {
        $date = Date::createFromString('2012/02/29 05:45:59 PM');
        $this->assertEquals('2012-02-29 17:45:59', $date->getFormattedDate(Date::FORMAT_YMD_HMS_WITH_DASHES));
    }

    public function testFromAM(): void
    {
        $date = Date::createFromString('2012/02/29 05:45:59 AM');
        $this->assertEquals('2012-02-29 05:45:59', $date->getFormattedDate(Date::FORMAT_YMD_HMS_WITH_DASHES));
    }

    public function testMDY_NotLeapyear(): void
    {
        $date = Date::createFromString('2013/02/29');
        $this->assertEquals('2013/03/01', $date->getFormattedDate(Date::FORMAT_YMD_WITH_SLASHES));
    }

    public function testMDY_00NotLeapyear(): void
    {
        $date = Date::createFromString('2100/02/29');
        $this->assertEquals('2100/03/01', $date->getFormattedDate(Date::FORMAT_YMD_WITH_SLASHES));
    }

    public function testMDY_00Leapyear(): void
    {
        $date = Date::createFromString('2000/02/29');
        $this->assertEquals('2000/02/29', $date->getFormattedDate(Date::FORMAT_YMD_WITH_SLASHES));
    }

    public function testGetHour(): void
    {
        $date = Date::createFromString('2018/01/01 14:34:00');
        $this->assertEquals('14', $date->getHour());
    }

    public function testGetMinute(): void
    {
        $date = Date::createFromString('2018/01/01 14:34:00');
        $this->assertEquals('34', $date->getMinute());
    }

    public function testGetSecond(): void
    {
        $date = Date::createFromString('2018/01/01 14:34:00');
        $this->assertEquals('00', $date->getSecond());
    }

    public function testSetAndGetSecond(): void
    {
        $date = Date::createFromString('2018/01/01 14:34:00');
        $date->setSecond(34);
        $this->assertEquals('34', $date->getSecond());
    }

    public function testSetAndGetMinute(): void
    {
        $date = Date::createFromString('2018/01/01 14:34:00');
        $date->setMinute(32);
        $this->assertEquals('32', $date->getMinute());
    }

    public function testSetAndGetHour(): void
    {
        $date = Date::createFromString('2018/01/01 14:34:00');
        $date->setHour(13);
        $this->assertEquals('13', $date->getHour());
    }

    public function testSetAndGetYear(): void
    {
        $date = Date::createFromString('2018/01/01 14:34:00');
        $date->setYear(1986);
        $this->assertEquals('1986', $date->getYear());
    }

    public function testSetAndGetMonth(): void
    {
        $date = Date::createFromString('2018/01/01 14:34:00');
        $date->setMonth(11);
        $this->assertEquals('11', $date->getMonth());
    }

    public function testSetAndGetDay(): void
    {
        $date = Date::createFromString('2018/01/01 14:34:00');
        $date->setDay(25);
        $this->assertEquals('25', $date->getDay());
    }

    public function testGetDayOfWeek(): void
    {
        $date = Date::createFromString('2021/03/22 14:34:00');
        $this->assertEquals('1', $date->getDayOfWeek());
    }

    public function testGetDayOfYear(): void
    {
        $date = Date::createFromString('2021/03/22 14:34:00');
        $this->assertEquals('80', $date->getDayOfYear());
    }

    public function testIsDaylightSavingsTime(): void
    {
        $date = Date::createFromString('2021/03/22 14:34:00');
        $date->setTimezone('America/Chicago');
        $this->assertTrue($date->isDaylightSavingsTime());
    }

    public function testIsNotDaylightSavingsTime(): void
    {
        $date = Date::createFromString('2021/02/22 14:34:00');
        $date->setTimezone('America/Chicago');
        $this->assertFalse($date->isDaylightSavingsTime());
    }

    public function testIsLeapYear(): void
    {
        $date = Date::createFromString('2020/03/22 14:34:00');
        $this->assertTrue($date->isLeapYear());
    }

    public function testIsNotLeapYear(): void
    {
        $date = Date::createFromString('2021/02/22 14:34:00');
        $this->assertFalse($date->isLeapYear());
    }

    public function testGetFormattedDate(): void
    {
        $date = Date::createFromString('2018/01/01 14:34:00');
        $this->assertEquals('01/01/2018 02:34:00', $date->getFormattedDate('m/d/Y h:i:s'));
    }

    public function testStringGet(): void
    {
        $date = Date::createFromString('01/02/2018 14:34');
        $this->assertEquals('2018-01-02 14:34:00', (string)$date);
    }

    public function testCreateFromTimestamp(): void
    {
        $timestamp = time();
        $date = Date::createFromTimestamp($timestamp);
        $this->assertEquals(date('Y-m-d H:i:s', $timestamp), (string)$date);
    }

    public function testCreateStringFormats(): void
    {
        $timestamp = time();
        $expectedFull = date('Y-m-d H:i:s', $timestamp);
        $expectedNoSeconds = date('Y-m-d H:i:00', $timestamp);
        $expectedNoTime = date('Y-m-d 00:00:00', $timestamp);
        $expectedTimeOnly = date('H:i:s', $timestamp);

        $date = Date::createFromString(date('Y-m-d H:i:s', $timestamp));
        $this->assertEquals($expectedFull, (string)$date, 'Y-m-d H:i:s failed');

        $date = Date::createFromString(date('Y-m-d h:i:s a', $timestamp));
        $this->assertEquals($expectedFull, (string)$date, 'Y-m-d h:i:s a failed');

        $date = Date::createFromString(date('Y-m-d h:i a', $timestamp));
        $this->assertEquals(date('Y-m-d H:i:00', $timestamp), (string)$date, 'Y-m-d h:i:a failed');

        $date = Date::createFromString(date('Y/m/d H:i:s', $timestamp));
        $this->assertEquals($expectedFull, (string)$date, 'Y/m/d H:i:s failed');

        $date = Date::createFromString(date('Y/m/d h:i:s a', $timestamp));
        $this->assertEquals($expectedFull, (string)$date, 'Y/m/d h:i:s a failed');

        $date = Date::createFromString(date('Y/m/d h:i a', $timestamp));
        $this->assertEquals($expectedNoSeconds, (string)$date, 'Y/m/d h:i:a failed');

        $date = Date::createFromString(date('Ymd h:i:s a', $timestamp));
        $this->assertEquals($expectedFull, (string)$date, 'Ymd h:i:s a failed');

        $date = Date::createFromString(date('Ymd h:i a', $timestamp));
        $this->assertEquals($expectedNoSeconds, (string)$date, 'Ymd H:i a failed');

        $date = Date::createFromString(date('Ymd H:i:s', $timestamp));
        $this->assertEquals($expectedFull, (string)$date, 'Ymd H:i:s failed');

        $date = Date::createFromString(date('Ymd His', $timestamp));
        $this->assertEquals($expectedFull, (string)$date, 'Ymd His failed');

        $date = Date::createFromString(date('YmdH:i:s', $timestamp));
        $this->assertEquals($expectedFull, (string)$date, 'YmdH:i:s failed');

        $date = Date::createFromString(date('Ymd', $timestamp));
        $this->assertEquals($expectedNoTime, (string)$date, 'Ymd failed');

        $date = Date::createFromString(date('Y-m-d H:i', $timestamp));
        $this->assertEquals($expectedNoSeconds, (string)$date, 'Y-m-d H:i failed');

        $date = Date::createFromString(date('Y-m-d', $timestamp));
        $this->assertEquals($expectedNoTime, (string)$date, 'Y-m-d failed');

        $date = Date::createFromString(date('H:i:s', $timestamp));
        $this->assertEquals(date('Y-m-d ') . $expectedTimeOnly, (string)$date, 'H:i:s failed');
        $this->expectException(InvalidDateException::class);
        Date::createFromString('2018-04-05 44:33:22');
    }

    public function testInvalidMonth(): void
    {
        $this->expectException(InvalidDateException::class);
        new Date('2018', '13', '04');
    }

    public function testInvalidHour(): void
    {
        $this->expectException(InvalidDateException::class);
        new Date('2018', '12', '04', '25');
    }

    public function testInvalidMinute(): void
    {
        $this->expectException(InvalidDateException::class);
        new Date('2018', '12', '04', '12', '72');
    }

    public function testInvalidSecond(): void
    {
        $this->expectException(InvalidDateException::class);
        new Date('2018', '12', '04', '12', '49', '72');
    }

    public function testShortMonthWith31Days(): void
    {
        $this->expectException(InvalidDateException::class);
        new Date('2018', '04', '31');
    }

    public function testLongMonthWith32Days(): void
    {
        $this->expectException(InvalidDateException::class);
        new Date('2018', '05', '32');
    }

    public function testInvalidLeapyear(): void
    {
        $this->expectException(InvalidDateException::class);
        new Date('2018', '02', '29');
    }

    public function testInvalidLeapCentury(): void
    {
        $this->expectException(InvalidDateException::class);
        new Date('3000', '02', '29');
    }

    public function testInvalidFormatOnManualCreate(): void
    {
        $this->expectException(InvalidDateException::class);
        new Date('AB', '12', '12');
    }

    public function testTimezoneStable(): void
    {
        $originalTimezone = date_default_timezone_get();
        new Date('2017', '04', '05', '04', '04', '04', 'America/Phoenix');
        $this->assertEquals($originalTimezone, date_default_timezone_get());
    }

    public function testInvalidFormat(): void
    {
        $this->expectException(InvalidDateException::class);
        Date::createFromString('2018-04/05 05:05:05');
    }

    public function testAddMinutesToDate(): void
    {
        $date = Date::createFromString('2018-01-01 00:00:00');
        $date->modify('+5 minutes');
        $this->assertEquals('2018-01-01 00:05:00', (string)$date);
    }

    public function testAddSecondsToDate(): void
    {
        $date = Date::createFromString('2018-01-01 00:00:00');
        $date->modify('+5 seconds');
        $this->assertEquals('2018-01-01 00:00:05', (string)$date);
    }

    public function testAddHoursToDate(): void
    {
        $date = Date::createFromString('2018-01-01 00:00:00');
        $date->modify('+5 hours');
        $this->assertEquals('2018-01-01 05:00:00', (string)$date);
    }

    public function testAddYearsToDate(): void
    {
        $date = Date::createFromString('2018-01-01 00:00:00');
        $date->modify('+1 year');
        $this->assertEquals('2019-01-01 00:00:00', (string)$date);
    }

    public function testAddMonthsToDate(): void
    {
        $date = Date::createFromString('2018-01-01 00:00:00');
        $date->modify('+1 month');
        $this->assertEquals('2018-02-01 00:00:00', (string)$date);
    }

    public function testAddDaysToDate(): void
    {
        $date = Date::createFromString('2018-01-01 00:00:00');
        $date->modify('+1 day');
        $this->assertEquals('2018-01-02 00:00:00', (string)$date);
    }

    public function testSubtractMinutesFromDate(): void
    {
        $date = Date::createFromString('2018-01-01 00:05:00');
        $date->modify('-5 minutes');
        $this->assertEquals('2018-01-01 00:00:00', (string)$date);
    }

    public function testSubtractSecondsFromDate(): void
    {
        $date = Date::createFromString('2018-01-01 00:00:05');
        $date->modify('-5 seconds');
        $this->assertEquals('2018-01-01 00:00:00', (string)$date);
    }

    public function testSubtractHoursFromDate(): void
    {
        $date = Date::createFromString('2018-01-01 05:00:00');
        $date->modify('-5 hours');
        $this->assertEquals('2018-01-01 00:00:00', (string)$date);
    }

    public function testSubtractYearsFromDate(): void
    {
        $date = Date::createFromString('2018-01-01 00:00:00');
        $date->modify('-1 year');
        $this->assertEquals('2017-01-01 00:00:00', (string)$date);
    }

    public function testSubtractMonthsFromDate(): void
    {
        $date = Date::createFromString('2018-02-01 00:00:00');
        $date->modify('-1 month');
        $this->assertEquals('2018-01-01 00:00:00', (string)$date);
    }

    public function testSubtractDaysFromDate(): void
    {
        $date = Date::createFromString('2018-01-02 00:00:00');
        $date->modify('-1 day');
        $this->assertEquals('2018-01-01 00:00:00', (string)$date);
    }

    public function testAddMonthsToDateWithRollover(): void
    {
        $date = Date::createFromString('2018-01-31 00:00:00');
        $date->modify('+1 month');
        $this->assertEquals('2018-03-03 00:00:00', (string)$date);
    }

    public function testBadDateModification(): void
    {
        $date = Date::createFromString('2018-01-31 00:00:00');
        $this->expectException(InvalidDateException::class);
        $date->modify('+1 blorgh');
    }

    public function testGetAsDateTime(): void
    {
        $date = Date::createFromString('2018-01-01 00:00:00');
        $this->assertInstanceOf(\DateTime::class, $date->getAsDateTime());
    }

    public function testCreateFromNow(): void
    {
        $date = Date::createFromNow();
        $this->assertInstanceOf(Date::class, $date);
    }

    public function testGetTimestamp(): void
    {
        $timestampExpected = mktime(0, 0, 0, 01, 01, 2020);
        $date = Date::createFromString('2020-01-01 00:00:00');
        $this->assertEquals($timestampExpected, $date->getTimestamp());
    }

    public function testGreaterThan(): void
    {
        $timestampGreater = Date::createFromString('2021-01-01 00:00:00');
        $timestampLower = Date::createFromString('2020-01-01 00:00:00');
        $this->assertTrue($timestampGreater->greaterThan($timestampLower));
    }

    public function testLessThan(): void
    {
        $timestampGreater = Date::createFromString('2021-01-01 00:00:00');
        $timestampLower = Date::createFromString('2020-01-01 00:00:00');
        $this->assertTrue($timestampLower->lessThan($timestampGreater));
    }

    public function testGreaterThanEqualGreater(): void
    {
        $timestampGreater = Date::createFromString('2021-01-01 00:00:00');
        $timestampLower = Date::createFromString('2020-01-01 00:00:00');
        $this->assertTrue($timestampGreater->greaterThanEqual($timestampLower));
    }

    public function testLessThanEqualLess(): void
    {
        $timestampGreater = Date::createFromString('2021-01-01 00:00:00');
        $timestampLower = Date::createFromString('2020-01-01 00:00:00');
        $this->assertTrue($timestampLower->lessThanEqual($timestampGreater));
    }

    public function testGreaterThanEqualEqual(): void
    {
        $timestampGreater = Date::createFromString('2021-01-01 00:00:00');
        $timestampLower = Date::createFromString('2021-01-01 00:00:00');
        $this->assertTrue($timestampGreater->greaterThanEqual($timestampLower));
    }

    public function testLessThanEqualEqual(): void
    {
        $timestampGreater = Date::createFromString('2021-01-01 00:00:00');
        $timestampLower = Date::createFromString('2021-01-01 00:00:00');
        $this->assertTrue($timestampLower->lessThanEqual($timestampGreater));
    }

    public function testTimezones(): void
    {
        $date = Date::createFromTimestamp(1594636792);
        $date->setTimezone('America/New_York');
        $this->assertEquals('2020-07-13 06:39:52', $date->getFormattedDate());
        $date->setTimezone('America/Chicago');
        $this->assertEquals('2020-07-13 05:39:52', $date->getFormattedDate());
    }
}
