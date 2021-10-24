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

namespace Feast\Logger;

use Exception;
use Feast\Enums\LogLevelCode;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\ErrorLoggerInterface;
use Feast\Interfaces\LoggerInterface;
use Feast\ServiceContainer\ContainerException;
use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;
use Throwable;

class ErrorLogger implements ServiceContainerItemInterface, ErrorLoggerInterface
{
    use DependencyInjected;

    /**
     * @throws ContainerException|NotFoundException
     */
    public function __construct(private LoggerInterface $logger)
    {
        $this->checkInjected();
        $this->init();
    }

    protected function init(): void
    {
        set_error_handler(
            [
                $this,
                'errorHandler'
            ]
        );
        set_exception_handler(
            [
                $this,
                'exceptionHandler'
            ]
        );
    }

    /**
     * Error handler for all errors (not exceptions).
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     */
    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        $errorMessage = $errstr . ' in ' . $errfile . ' on line ' . (string)$errline . "\n";
        $errorMessage .= $this->generateCallTrace();

        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $this->logger->notice($errorMessage);
                break;
            case E_ERROR:
            case E_USER_ERROR:
                $this->logger->error($errorMessage);
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $this->logger->info($errorMessage);
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $this->logger->warning($errorMessage);
                break;
            default:
                $this->logger->emergency($errorMessage);
        }

        return true;
    }

    /**
     * Exception handler.
     *
     * @param Throwable $exception
     * @param bool $caught
     * @return bool
     * @throws NotFoundException
     */
    public function exceptionHandler(Throwable $exception, bool $caught = false): bool
    {
        $type = $caught ? 'Caught ' : 'Uncaught ';
        $errorMessage = $type . get_class($exception) . ' - ' . $exception->getMessage() . ' in ' . $exception->getFile(
            ) . ' on line ' . (string)$exception->getLine();

        $this->logger->error($errorMessage);
        $this->logger->rawLog(LogLevelCode::ERROR, $exception->getTraceAsString());
        $newException = new ServerFailureException(
            $exception->getMessage(),
            null,
            (int)$exception->getCode(),
            $exception
        );
        $newException->printParentException();

        return true;
    }

    protected function generateCallTrace(): string
    {
        $e = new Exception();
        $trace = explode("\n", $e->getTraceAsString());

        array_shift($trace); // remove call to this method
        array_shift($trace); // remove call to previous method

        $return = [];
        $count = 0;
        foreach ($trace as $traceItem) {
            $firstSpace = strpos($traceItem, ' ');
            if (($firstSpace !== false)) {
                $traceData = substr($traceItem, $firstSpace);
                $return[] = '#' . (string)$count++ . $traceData;
            }
        }

        return implode("\n", $return);
    }

}
