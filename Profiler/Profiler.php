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

namespace Feast\Profiler;

use Feast\Interfaces\ProfilerInterface;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;

/**
 * Profiler class, used to record time of execution. Can be used to report total
 * time at the end of the view render, or in direct output from controllers for
 * development purposes.
 */
class Profiler implements ServiceContainerItemInterface, ProfilerInterface
{
    use DependencyInjected;

    public function __construct(private float $startTime, bool $checkInjected = true)
    {
        if ($checkInjected) {
            $this->checkInjected();
        }
    }

    /**
     * Return the total time of execution up to this point.
     *
     * @return string
     */
    public function getTotalTime(): string
    {
        if (function_exists('bcsub')) {
            return bcsub((string)microtime(true), (string)$this->startTime, 4);
        }
        return $this->getTotalTimeNoBcMath((string)microtime(true), (string)$this->startTime, 4);
    }

    /**
     * Get total time if BC math not enabled. Takes microtime as end and start inputs.
     *
     * @param string $endTime
     * @param string $startTime
     * @param int $scale
     * @return string
     */
    public function getTotalTimeNoBcMath(string $endTime, string $startTime, int $scale = 4): string
    {
        $startTimeParts = $this->getTimeParts($startTime);
        $endTimeParts = $this->getTimeParts($endTime);

        $resultPartial = (int)$endTimeParts[1] - (int)$startTimeParts[1];
        $resultMain = (int)$endTimeParts[0] - (int)$startTimeParts[0];

        if ($resultPartial < 0) {
            $resultMain--;
            $resultPartial = (int)(str_pad('1', strlen((string)$resultPartial), '0')) + $resultPartial;
        }
        if (strlen((string)$resultPartial) > $scale) {
            $resultPartial = (int)substr((string)$resultPartial, 0, $scale);
        }
        return (string)$resultMain . '.' . str_pad((string)$resultPartial, $scale, '0');
    }

    protected function getTimeParts(string $time): array
    {
        $return = explode('.', $time);
        $return[1] ??= '0';
        return $return;
    }

    /**
     * Get total current memory usage.
     *
     * @param bool $realUsage
     * @return array{G?: int|float,m?: int|float,k?: int|float,b?: int|float,readable: string}
     */
    public function getMemoryUsage(bool $realUsage = false): array
    {
        $memory = memory_get_usage($realUsage);

        return $this->formatMemoryUsage($memory);
    }

    /**
     * Get total current memory usage in a readable format.
     *
     * @param bool $realUsage
     * @return string
     */
    public function getReadableMemoryUsage(bool $realUsage = false): string
    {
        $memory = $this->getMemoryUsage($realUsage);
        return $memory['readable'];
    }

    /**
     * Get peak memory usage.
     *
     * @param bool $realUsage
     * @return array{G?: int|float,m?: int|float,k?: int|float,b?: int|float,readable: string}
     */
    public function getPeakMemoryUsage(bool $realUsage = false): array
    {
        $memory = memory_get_peak_usage($realUsage);

        return $this->formatMemoryUsage($memory);
    }

    /**
     * Get total peak memory usage in a readable format.
     *
     * @param bool $realUsage
     * @return string
     */
    public function getReadablePeakMemoryUsage(bool $realUsage = false): string
    {
        $memory = $this->getPeakMemoryUsage($realUsage);
        return $memory['readable'];
    }

    /**
     * Organize memory into an array for ease of multiple formatting.
     *
     * @param int $memory
     * @return array{G?: int|float,m?: int|float,k?: int|float,b?: int|float,readable: string}
     */
    protected function formatMemoryUsage(int $memory): array
    {
        $return = [];
        $kilobyteSize = 1024;
        $megabyteSize = 1_048_576;
        $gigaByteSize = 1_073_741_824;
        if ($memory > $gigaByteSize) {
            $return['G'] = (int)floor($memory / ($gigaByteSize));
            $return['readable'] = (string)(round($memory / ($gigaByteSize), 3)) . 'GB';
            $memory = $memory % ($gigaByteSize);
        }
        if ($memory > $megabyteSize) {
            $return['m'] = (int)floor($memory / ($megabyteSize));
            $return['readable'] ??= (string)(round($memory / ($megabyteSize), 3)) . 'MB';
            $memory = $memory % ($megabyteSize);
        }
        if ($memory > $kilobyteSize) {
            $return['k'] = (int)floor($memory / ($kilobyteSize));
            $return['readable'] ??= (string)(round($memory / ($kilobyteSize), 3)) . 'k';
            $memory = $memory % $kilobyteSize;
        }
        if ($memory != 0) {
            $return['b'] = $memory;
            $return['readable'] ??= (string)($memory) . 'b';
        }
        $return['readable'] ??= '';
        return $return;
    }

}
