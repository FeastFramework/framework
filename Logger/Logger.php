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

namespace Feast\Logger;

use Feast\Enums\LogLevelCode;
use Feast\Interfaces\ConfigInterface;
use Feast\Main;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use Throwable;

class Logger implements LoggerInterface, ServiceContainerItemInterface, \Feast\Interfaces\LoggerInterface
{

    use DependencyInjected;

    private const LOG_DIR = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
    private ?int $logLevel;

    public function __construct(private ConfigInterface $config, private string $runAs)
    {
        $this->checkInjected();
        $this->logLevel = $this->getLevelFromString(
            (string)$this->config->getSetting('log.level', LogLevel::ERROR)
        );
        $this->makeLogDirIfNotExists();
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevelCode::EMERGENCY, 'EMERGENCY: ' . $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     */
    public function alert($message, array $context = []): void
    {
        $this->log(LogLevelCode::ALERT, 'ALERT: ' . $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     */
    public function critical($message, array $context = []): void
    {
        $this->log(LogLevelCode::CRITICAL, 'CRITICAL: ' . $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = []): void
    {
        $this->log(LogLevelCode::ERROR, 'ERROR: ' . $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = []): void
    {
        $this->log(LogLevelCode::WARNING, 'WARNING: ' . $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevelCode::NOTICE, 'NOTICE: ' . $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = []): void
    {
        $this->log(LogLevelCode::INFO, 'INFO: ' . $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     */
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevelCode::DEBUG, 'DEBUG: ' . $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = []): void
    {
        if (is_string($level)) {
            $message = strtoupper($level) . ': ' . $message;
            $level = $this->getLevelFromString($level);
        }
        if ($level < $this->logLevel) {
            return;
        }
        $message = $this->interpolateContext($message, $context);
        $this->rawLog((int)$level, (date('[Y-m-d H:i:s] ')) . trim($message));
        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $exception = $context['exception'];

            $this->error($exception->getMessage());
            $this->error($exception->getTraceAsString());
        }
        if ($this->runAs === Main::RUN_AS_CLI) {
            echo $message . "\n";
        }
    }

    /**
     * @param Stringable|string $message
     * @param array $context
     * @return string
     */
    protected function interpolateContext(Stringable|string $message, array $context): string
    {
        $replace = [];
        /**
         * @var string $key
         * @var string|int|bool|float|object|array|null $val
         */
        foreach ($context as $key => $val) {
            if ($val === null || is_scalar($val) || $val instanceof Stringable) {
                $replace['{' . $key . '}'] = (string)$val;
            }
        }
        return strtr((string)$message, $replace);
    }

    /**
     * @param int $level
     * @param string $message
     */
    public function rawLog(int $level, string $message): void
    {
        if ($level < $this->logLevel) {
            return;
        }
        $fileName = self::LOG_DIR . 'feast.log';
        if (!file_exists($fileName)) {
            touch($fileName);
            chmod($fileName, (int)$this->config->getSetting('log.permissions.file', 0666));
        }
        file_put_contents(
            $fileName,
            trim($message) . "\n",
            FILE_APPEND
        );
    }

    protected function makeLogDirIfNotExists(): void
    {
        if (!is_dir(self::LOG_DIR)) {
            mkdir(
                self::LOG_DIR,
                (int)$this->config->getSetting('log.permissions.path', 0755),
                true
            );
        }
    }

    private function getLevelFromString(string $level): int
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return match (strtolower($level)) {
            LogLevel::DEBUG => LogLevelCode::DEBUG,
            LogLevel::INFO => LogLevelCode::INFO,
            LogLevel::NOTICE => LogLevelCode::NOTICE,
            LogLevel::WARNING => LogLevelCode::WARNING,
            LogLevel::ERROR => LogLevelCode::ERROR,
            LogLevel::CRITICAL => LogLevelCode::CRITICAL,
            LogLevel::ALERT => LogLevelCode::ALERT,
            LogLevel::EMERGENCY => LogLevelCode::EMERGENCY,
            default => throw new InvalidArgumentException('Invalid error type - ' . $level)
        };
    }
}
