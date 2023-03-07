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

namespace Mocks;

use Feast\Enums\LogLevelCode;
use Feast\Interfaces\LoggerInterface;

class LoggerMock implements LoggerInterface
{

    public function emergency($message, array $context = []): void
    {
        echo $message;
    }

    public function alert($message, array $context = []): void
    {
        echo $message;
    }

    public function critical($message, array $context = []): void
    {
        echo $message;
    }

    public function error($message, array $context = []): void
    {
        echo $message;
    }

    public function warning($message, array $context = []): void
    {
        echo $message;
    }

    public function notice($message, array $context = []): void
    {
        echo $message;
    }

    public function info($message, array $context = []): void
    {
        echo $message;
    }

    public function debug($message, array $context = []): void
    {
        echo $message;
    }

    public function log($level, $message, array $context = []): void
    {
        echo $message;
    }

    public function rawLog(int $level, string $message): void
    {
        echo $message;
    }
}
