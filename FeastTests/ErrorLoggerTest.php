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

use Feast\Logger\ErrorLogger;
use PHPUnit\Framework\TestCase;

class ErrorLoggerTest extends TestCase
{

    public function testErrorHandlerNotice(): void
    {
        $loggerInterface = new \Mocks\LoggerMock();
        $errorLogger = new ErrorLogger($loggerInterface);
        $this->expectOutputRegex('/test in feast on line 2.*/');
        $errorLogger->errorHandler(E_NOTICE,'test','feast',2);
    }

    public function testErrorHandlerError(): void
    {
        $loggerInterface = new \Mocks\LoggerMock();
        $errorLogger = new ErrorLogger($loggerInterface);
        $this->expectOutputRegex('/test in feast on line 2.*/');
        $errorLogger->errorHandler(E_ERROR,'test','feast',2);
    }

    public function testErrorHandlerDeprecated(): void
    {
        $loggerInterface = new \Mocks\LoggerMock();
        $errorLogger = new ErrorLogger($loggerInterface);
        $this->expectOutputRegex('/test in feast on line 2.*/');
        $errorLogger->errorHandler(E_DEPRECATED,'test','feast',2);
    }

    public function testErrorHandlerWarning(): void
    {
        $loggerInterface = new \Mocks\LoggerMock();
        $errorLogger = new ErrorLogger($loggerInterface);
        $this->expectOutputRegex('/test in feast on line 2.*/');
        $errorLogger->errorHandler(E_WARNING,'test','feast',2);
    }

    public function testErrorHandlerEmergency(): void
    {
        $loggerInterface = new \Mocks\LoggerMock();
        $errorLogger = new ErrorLogger($loggerInterface);
        $this->expectOutputRegex('/test in feast on line 2.*/');
        $errorLogger->errorHandler(E_ALL,'test','feast',2);
    }

    public function testExceptionHandlerCaught(): void
    {
        $container = di(null,\Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(\Feast\Interfaces\ConfigInterface::class,$this->createStub(\Feast\Config\Config::class));
        $exception = new Exception('TestError');

        $loggerInterface = new \Mocks\LoggerMock();
        $errorLogger = new ErrorLogger($loggerInterface);
        $this->expectOutputRegex('/Caught Exception - TestError.*/');
        $errorLogger->exceptionHandler($exception,true);

    }

    public function testExceptionHandlerUncaught(): void
    {
        $container = di(null,\Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(\Feast\Interfaces\ConfigInterface::class,$this->createStub(\Feast\Config\Config::class));
        $exception = new Exception('TestError');

        $loggerInterface = new \Mocks\LoggerMock();
        $errorLogger = new ErrorLogger($loggerInterface);
        $this->expectOutputRegex('/Uncaught Exception - TestError.*/');
        $errorLogger->exceptionHandler($exception);

    }
}
