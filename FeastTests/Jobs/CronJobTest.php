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

namespace Jobs;

use Feast\Date;
use Feast\Interfaces\ConfigInterface;
use Feast\Jobs\CronJob;
use PHPUnit\Framework\TestCase;

class CronJobTest extends TestCase
{

    # ┌───────────── minute (0 - 59)
    # │ ┌───────────── hour (0 - 23)
    # │ │ ┌───────────── day of the month (1 - 31)
    # │ │ │ ┌───────────── month (1 - 12)
    # │ │ │ │ ┌───────────── day of the week (0 - 6) (Sunday to Saturday;
    # │ │ │ │ │                                   7 is also Sunday on some systems)
    # │ │ │ │ │
    # │ │ │ │ │
    # * * * * * <command to execute>

    public function setUp(): void
    {
        \Feast\Jobs\FileData::reset();
    }

    public function testShouldRunHourlyTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->environment('production')->cron('@hourly')->shouldRun(
            Date::createFromString('2020-07-13 04:00:32'),
            $config
        );
        $this->assertTrue($run);
    }

    public function testShouldRunHourlyFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@hourly')->shouldRun(Date::createFromString('2020-07-13 04:02:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunDailyTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@daily')->shouldRun(Date::createFromString('2020-07-13 00:00:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunDailyFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@daily')->shouldRun(Date::createFromString('2020-07-13 00:01:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunDailyWrongHourFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@daily')->shouldRun(Date::createFromString('2020-07-13 01:00:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunMonthlyTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@monthly')->shouldRun(Date::createFromString('2020-02-01 00:00:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunMonthlyFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@monthly')->shouldRun(Date::createFromString('2020-01-02 00:00:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunMonthlyWrongHourFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@monthly')->shouldRun(Date::createFromString('2020-02-01 01:00:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunWeeklyTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@weekly')->shouldRun(Date::createFromString('2021-03-07 00:00:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunWeeklyFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@weekly')->shouldRun(Date::createFromString('2021-03-08 00:00:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunWeeklyWrongHourFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@weekly')->shouldRun(Date::createFromString('2021-03-07 01:00:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunYearlyTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@yearly')->shouldRun(Date::createFromString('2020-01-01 00:00:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunYearlyFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@yearly')->shouldRun(Date::createFromString('2020-02-01 00:00:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunYearlyWrongHourFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('@yearly')->shouldRun(Date::createFromString('2020-02-01 01:00:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunWithSimpleLogicTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('1 1 * * *')->shouldRun(Date::createFromString('2020-02-01 01:01:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunWithSimpleLogicSundayZeroTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* * * * 0')->shouldRun(Date::createFromString('2021-03-07 01:01:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunWithSimpleLogicSundaySevenTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* * * * 7')->shouldRun(Date::createFromString('2021-03-07 01:01:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunOddHoursTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/2 * * *')->shouldRun(Date::createFromString('2021-03-07 09:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunOddHoursFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/2 * * *')->shouldRun(Date::createFromString('2021-03-07 08:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEvenHoursTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 0-23/2 * * *')->shouldRun(Date::createFromString('2021-03-07 08:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunEvenHoursFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 0-23/2 * * *')->shouldRun(Date::createFromString('2021-03-07 09:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursOneTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 01:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunEveryThreeHoursTwoFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 02:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursThreeFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 03:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursFourTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 04:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunEveryThreeHoursFiveFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 05:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursSixFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 06:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursSevenTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 07:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunEveryThreeHoursEightFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 08:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursNineFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 09:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursTenTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 10:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunEveryThreeHoursElevenFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 11:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursTwelveFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 12:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursThirteenTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 13:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunEveryThreeHoursFourteenFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 14:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursFifteenFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 15:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursSixteenTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 16:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunEveryThreeHoursSeventeenFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 17:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursEighteenFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 18:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursNineteenTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 19:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunEveryThreeHoursTwentyFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 20:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursTwentyOneFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 21:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursTwentyTwoTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');

        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 22:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunEveryThreeHoursTwentyThreeFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 23:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunEveryThreeHoursZeroFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* 1-23/3 * * *')->shouldRun(Date::createFromString('2021-03-07 00:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunWithDivisorsTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('*/5 */3 * * *')->shouldRun(Date::createFromString('2021-03-07 09:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunWithDivisorsFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('*/5 */3 * * *')->shouldRun(Date::createFromString('2021-03-07 09:26:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunWithDivisorsZeroFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('*/5 */0 * * *')->shouldRun(Date::createFromString('2021-03-07 09:25:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunWithDivisorsZeroTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('*/5 */0 * * *')->shouldRun(Date::createFromString('2021-03-07 00:25:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunWithSuperConvolutedWhyWouldYouDoThisCronExpressionMinuteTwentyThreeTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('23,24/5,5-15/5 4 * * *')->shouldRun(
            Date::createFromString('2021-03-07 04:23:32'),
            $config
        );
        $this->assertTrue($run);
    }

    public function testShouldRunWithSuperConvolutedWhyWouldYouDoThisCronExpressionMinuteThirtyFiveTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('23,24/5,5-35/5 4 * * *')->shouldRun(
            Date::createFromString('2021-03-07 04:35:32'),
            $config
        );
        $this->assertTrue($run);
    }

    public function testShouldRunWithSuperConvolutedWhyWouldYouDoThisCronExpressionMinuteFourteenTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('23,*/7,24/5,5-15/5 4 * * *')->shouldRun(
            Date::createFromString('2021-03-07 04:14:32'),
            $config
        );
        $this->assertTrue($run);
    }

    public function testShouldRunWithSuperConvolutedWhyWouldYouDoThisCronExpressionMinuteFiveTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('23,24/5,5-15/5 4 * * *')->shouldRun(
            Date::createFromString('2021-03-07 04:05:32'),
            $config
        );
        $this->assertTrue($run);
    }

    public function testShouldRunWithSuperConvolutedWhyWouldYouDoThisCronExpressionMinuteSixFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('23,24/5,5-15/5 4 * * *')->shouldRun(
            Date::createFromString('2021-03-07 04:06:32'),
            $config
        );
        $this->assertFalse($run);
    }

    public function testShouldRunWithSimpleLogicFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('1 2 * * *')->shouldRun(Date::createFromString('2020-02-01 01:01:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunWithOverlapTrueThenFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $now = Date::createFromString('2020-02-01 01:01:32');
        $run = $cron->cron('1 1 * * *')->withoutOverlapping()->shouldRun($now, $config);
        $this->assertTrue($run);
        $this->assertEquals(1440, $cron->getOverlapTime());
        $cron->startRun($now);
        $run = $cron->shouldRun($now, $config);
        $this->assertFalse($run);
        $cron->stopRun($now);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('txt' . (string)$now->getTimestamp(), $output);
    }

    public function testHourlyCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->hourly();
        $this->assertEquals('0 * * * *', $cron->getCronString());
    }

    public function testHourlyAtCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->hourlyAt(30);
        $this->assertEquals('30 * * * *', $cron->getCronString());
    }

    public function testHourlyOddCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->hourlyOnOddHours();
        $this->assertEquals('0 1-23/2 * * *', $cron->getCronString());
    }

    public function testEveryTwoHoursCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyTwoHours();
        $this->assertEquals('0 */2 * * *', $cron->getCronString());
    }

    public function testEveryThreeHoursCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyThreeHours();
        $this->assertEquals('0 */3 * * *', $cron->getCronString());
    }

    public function testEveryFourHoursCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyFourHours();
        $this->assertEquals('0 */4 * * *', $cron->getCronString());
    }

    public function testEverySixHoursCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everySixHours();
        $this->assertEquals('0 */6 * * *', $cron->getCronString());
    }

    public function testEveryEightHoursCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyEightHours();
        $this->assertEquals('0 */8 * * *', $cron->getCronString());
    }

    public function testEveryTwelveHoursCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyTwelveHours();
        $this->assertEquals('0 */12 * * *', $cron->getCronString());
    }

    public function testEveryMinuteCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyMinute();
        $this->assertEquals('* * * * *', $cron->getCronString());
    }

    public function testEveryOddMinuteCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyOddMinute();
        $this->assertEquals('1-59/2 * * * *', $cron->getCronString());
    }

    public function testEveryTwoMinuteCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyTwoMinutes();
        $this->assertEquals('*/2 * * * *', $cron->getCronString());
    }

    public function testEveryThreeMinuteCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyThreeMinutes();
        $this->assertEquals('*/3 * * * *', $cron->getCronString());
    }

    public function testEveryFourMinuteCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyFourMinutes();
        $this->assertEquals('*/4 * * * *', $cron->getCronString());
    }

    public function testEveryFiveMinuteCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyFiveMinutes();
        $this->assertEquals('*/5 * * * *', $cron->getCronString());
    }

    public function testEveryTenMinuteCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyTenMinutes();
        $this->assertEquals('*/10 * * * *', $cron->getCronString());
    }

    public function testEveryFifteenMinuteCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyFifteenMinutes();
        $this->assertEquals('*/15 * * * *', $cron->getCronString());
    }

    public function testEveryTwentyMinuteCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyTwentyMinutes();
        $this->assertEquals('*/20 * * * *', $cron->getCronString());
    }

    public function testEveryThirtyMinuteCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->everyThirtyMinutes();
        $this->assertEquals('*/30 * * * *', $cron->getCronString());
    }

    public function testDailyCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->daily();
        $this->assertEquals('0 0 * * *', $cron->getCronString());
    }

    public function testDailyAtCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->dailyAt('14:55');
        $this->assertEquals('55 14 * * *', $cron->getCronString());
    }

    public function testTwiceDailyAtCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->twiceDaily(7, 14);
        $this->assertEquals('0 7,14 * * *', $cron->getCronString());
    }

    public function testWeeklyCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->weekly();
        $this->assertEquals('0 0 * * 0', $cron->getCronString());
    }

    public function testWeeklyOnCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->weeklyOn(2, '14:52');
        $this->assertEquals('52 14 * * 2', $cron->getCronString());
    }

    public function testWeeklyOnNoTimeCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->weeklyOn(2);
        $this->assertEquals('0 0 * * 2', $cron->getCronString());
    }

    public function testMonthlyCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->monthly();
        $this->assertEquals('0 0 1 * *', $cron->getCronString());
    }

    public function testMonthlyOnCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->monthlyOn(2, '14:52');
        $this->assertEquals('52 14 2 * *', $cron->getCronString());
    }

    public function testMonthlyOnNoTimeCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->monthlyOn(2);
        $this->assertEquals('0 0 2 * *', $cron->getCronString());
    }

    public function testTwiceMonthlyCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->twiceMonthly(2, 7, '14:52');
        $this->assertEquals('52 14 2,7 * *', $cron->getCronString());
    }

    public function testTwiceMonthlyNoTimeCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->twiceMonthly(2, 7);
        $this->assertEquals('0 0 2,7 * *', $cron->getCronString());
    }

    public function testYearlyCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->yearly();
        $this->assertEquals('0 0 1 1 *', $cron->getCronString());
    }

    public function testYearlyOnCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->yearlyOn(4, 6, '12:34');
        $this->assertEquals('34 12 4 6 *', $cron->getCronString());
    }

    public function testYearlyOnWithoutTimeCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->yearlyOn(4, 6);
        $this->assertEquals('0 0 4 6 *', $cron->getCronString());
    }

    public function testQuarterlyCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->quarterly();
        $this->assertEquals('0 0 1 1,4,7,10 *', $cron->getCronString());
    }

    public function testWeekdaysCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->weekdays();
        $this->assertEquals('* * * * 1,2,3,4,5', $cron->getCronString());
    }

    public function testWeekendsCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->weekends();
        $this->assertEquals('* * * * 0,6', $cron->getCronString());
    }

    public function testSundaysCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->sundays();
        $this->assertEquals('* * * * 0', $cron->getCronString());
    }

    public function testMondaysCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->mondays();
        $this->assertEquals('* * * * 1', $cron->getCronString());
    }

    public function testTuesdaysCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->tuesdays();
        $this->assertEquals('* * * * 2', $cron->getCronString());
    }

    public function testWednesdaysCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->wednesdays();
        $this->assertEquals('* * * * 3', $cron->getCronString());
    }

    public function testThursdaysCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->thursdays();
        $this->assertEquals('* * * * 4', $cron->getCronString());
    }

    public function testFridaysCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->fridays();
        $this->assertEquals('* * * * 5', $cron->getCronString());
    }

    public function testSaturdaysCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->saturdays();
        $this->assertEquals('* * * * 6', $cron->getCronString());
    }

    public function testDaysCron(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $cron->days([1, 2]);
        $this->assertEquals('* * * * 1,2', $cron->getCronString());
    }

    public function testShouldRunWithBetweenTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->between('00:00', '01:05')->shouldRun(Date::createFromString('2020-02-01 01:01:32'), $config);
        $this->assertTrue($run);
    }

    public function testShouldRunWithBetweenFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->between('00:00', '01:05')->shouldRun(Date::createFromString('2020-02-01 01:07:32'), $config);
        $this->assertFalse($run);
    }

    public function testShouldRunNotBetweenTrue(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->unlessBetween('00:00', '01:05')->shouldRun(
            Date::createFromString('2020-02-01 01:06:32'),
            $config
        );
        $this->assertTrue($run);
    }

    public function testShouldRunNotBetweenFalse(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->unlessBetween('00:00', '01:05')->shouldRun(
            Date::createFromString('2020-02-01 01:02:32'),
            $config
        );
        $this->assertFalse($run);
    }

    public function testTimezoneTrue(): void
    {
        $date = Date::createFromTimestamp(1594636792);
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('39 5 * * *')->timezone('America/Chicago')->shouldRun($date, $config);
        $this->assertTrue($run);
    }

    public function testTimezoneFalse(): void
    {
        $date = Date::createFromTimestamp(1594636792);
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('39 6 * * *')->timezone('America/Chicago')->shouldRun($date, $config);
        $this->assertFalse($run);
    }

    public function testWhenTrue(): void
    {
        $date = Date::createFromTimestamp(1594636792);
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* * * * *')->when(true)->shouldRun($date, $config);
        $this->assertTrue($run);
    }

    public function testWhenFalse(): void
    {
        $date = Date::createFromTimestamp(1594636792);
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* * * * *')->when(1 === 3)->shouldRun($date, $config);
        $this->assertFalse($run);
    }

    public function testMaintenanceModeFalse(): void
    {
        \Feast\Jobs\file_put_contents(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'maintenance.txt', 1);
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* * * * *')->shouldRun(Date::createFromNow(), $config);
        $this->assertFalse($run);
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('maintenance.txt1', $output);
    }

    public function testMaintenanceModeTrue(): void
    {
        \Feast\Jobs\file_put_contents(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'maintenance.txt', 1);
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $run = $cron->cron('* * * * *')->evenInMaintenanceMode()->shouldRun(Date::createFromNow(), $config);
        $this->assertTrue($run);
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('maintenance.txt1', $output);
    }
    
    public function testBackgroundable(): void
    {
        $cron = $this->getMockForAbstractClass(CronJob::class);
        $config = $this->createStub(ConfigInterface::INTERFACE_NAME);
        $config->method('getEnvironmentName')->willReturn('production');
        $this->assertFalse($cron->isBackgroundable());
        $cron->cron('* * * * *')->runInBackground()->shouldRun(Date::createFromNow(), $config);
        $this->assertTrue($cron->isBackgroundable());
        
    }

}
