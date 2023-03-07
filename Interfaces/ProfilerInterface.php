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

namespace Feast\Interfaces;

use Feast\ServiceContainer\ServiceContainerItemInterface;

/**
 * Profiler class, used to record time of execution. Can be used to report total
 * time at the end of the view render, or in direct output from various classes
 * for development purposes.
 */
interface ProfilerInterface extends ServiceContainerItemInterface
{
    final public const INTERFACE_NAME = self::class;

    /**
     * Return the total time of execution up to this point.
     *
     * @return string
     */
    public function getTotalTime(): string;

    /**
     * Get total current memory usage.
     *
     * @param bool $realUsage
     * @return array
     */
    public function getMemoryUsage(bool $realUsage = false): array;

    /**
     * Get peak memory usage.
     *
     * @param bool $realUsage
     * @return array
     */
    public function getPeakMemoryUsage(bool $realUsage = false): array;

    /**
     * Get total current memory usage in a readable format.
     *
     * @param bool $realUsage
     * @return string
     */
    public function getReadableMemoryUsage(bool $realUsage = false): string;

    /**
     * Get total peak memory usage in a readable format.
     *
     * @param bool $realUsage
     * @return string
     */
    public function getReadablePeakMemoryUsage(bool $realUsage = false): string;

}
