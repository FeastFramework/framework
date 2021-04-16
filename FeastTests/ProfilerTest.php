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

use PHPUnit\Framework\TestCase;

class ProfilerTest extends TestCase
{

    public function testGetMemoryUsage(): void
    {
        $profiler = new Feast\Profiler\Profiler(1613250113.0);
        $this->assertEquals(
            [
                'm' => 551,
                'G' => 9,
                'k' => 640,
                'b' => 1,
                'readable' => '9.539GB'

            ],
            $profiler->getMemoryUsage(true)
        );
        $this->assertEquals('9.539GB', $profiler->getReadableMemoryUsage(true));

        $this->assertEquals(
            [
                'k' => 971,
                'b' => 145,
                'm' => 976,
                'readable' => '976.948MB'
            ],
            $profiler->getMemoryUsage(false)
        );
        $this->assertEquals('976.948MB', $profiler->getReadableMemoryUsage(false));
    }

    public function testGetTotalTime(): void
    {
        $profiler = new Feast\Profiler\Profiler(1613250113.0);
        $this->assertEquals('10.1000', $profiler->getTotalTime());
    }

    public function testGetTotalTimeNoBcMath(): void
    {
        Feast\Profiler\ProfilerSetting::$useBcMath = false;
        $profiler = new Feast\Profiler\Profiler(1613250113.0);
        $this->assertEquals('10.1000', $profiler->getTotalTime());
        Feast\Profiler\ProfilerSetting::$useBcMath = true;
    }

    public function testSubtractTime(): void
    {
        $profiler = new Feast\Profiler\Profiler(1613250113.0);
        $result = $profiler->getTotalTimeNoBcMath('1613250123.0', '1613250113.1', 4);

        $this->assertEquals('9.9000', $result);
        $this->assertEquals(bcsub('1613250123.0', '1613250113.1', 4), $result);
    }

    public function testSubtractTimeLarger(): void
    {
        $profiler = new Feast\Profiler\Profiler(1613250113.0);
        $startTime = '1613250113.11111';
        $endTime = '1613250123.35434';
        $result = $profiler->getTotalTimeNoBcMath($endTime, $startTime, 4);
        $this->assertEquals(bcsub((string)$endTime, (string)$startTime, 4), $result);
    }

    public function testSubtractTimeLargerTestTwo(): void
    {
        $profiler = new Feast\Profiler\Profiler(1613250113.0);
        $startTime = '1613250113.11111';
        $endTime = '1613250123.75477';
        $result = $profiler->getTotalTimeNoBcMath($endTime, $startTime, 4);
        $this->assertEquals(bcsub((string)$endTime, (string)$startTime, 4), $result);
    }

    public function testGetPeakMemoryUsage(): void
    {
        $profiler = new Feast\Profiler\Profiler(1613250113.0);
        $this->assertEquals(
            [
                'm' => 551,
                'G' => 9,
                'k' => 640,
                'b' => 1,
                'readable' => '9.539GB'
            ],
            $profiler->getPeakMemoryUsage(true)
        );
        $this->assertEquals('9.539GB', $profiler->getReadablePeakMemoryUsage(true));
        $this->assertEquals(
            [
                'm' => 976,
                'k' => 1007,
                'b' => 941,
                'readable' => '976.984MB'
            ],
            $profiler->getPeakMemoryUsage(false)
        );
        $this->assertEquals('976.984MB', $profiler->getReadablePeakMemoryUsage(false));
    }

    public function testNoReconstruct(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $profiler = new Feast\Profiler\Profiler(1613250113.0);
        $container->add(\Feast\Interfaces\ProfilerInterface::class, $profiler);
        $this->expectException(\Feast\ServiceContainer\ContainerException::class);
        $profiler = new Feast\Profiler\Profiler(1613250113.0);
    }

    public function tearDown(): void
    {
        di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
    }
}
