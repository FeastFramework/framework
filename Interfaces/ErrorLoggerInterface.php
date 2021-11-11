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
use Throwable;

interface ErrorLoggerInterface extends ServiceContainerItemInterface
{
    /**
     * Error handler for all errors (not exceptions).
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     */
    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline): bool;

    /**
     * Exception handler.
     *
     * @param Throwable $exception
     * @param bool $caught
     * @return bool
     */
    public function exceptionHandler(Throwable $exception, bool $caught = false): bool;
}
