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

namespace Exception;

use Feast\Enums\ResponseCode;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\RequestInterface;
use Feast\Interfaces\ResponseInterface;
use Feast\Main;
use Feast\ServiceContainer\ServiceContainer;
use PHPUnit\Framework\TestCase;

class ServerFailureExceptionTest extends TestCase
{

    public function testPrintErrorJson(): void
    {
        $this->buildContainer('json', true);

        $exception = new ServerFailureException('Test', ResponseCode::HTTP_CODE_302, 5, null, Main::RUN_AS_CLI);

        $this->expectOutputString('{"error":{"message":"Test","code":5}}');
        $exception->printError();
    }

    public function testPrintError(): void
    {
        $this->buildContainer(setting: true);
        $exception = new ServerFailureException('Test', ResponseCode::HTTP_CODE_302, 0, null, Main::RUN_AS_WEBAPP);

        $this->expectOutputRegex('/Test<br \/>.*/');
        $exception->printError();
    }

    public function testPrintParentExceptionCliNoParent(): void
    {
        $this->buildContainer('json', true);
        $exception = new ServerFailureException('Test', ResponseCode::HTTP_CODE_302, 5, null, Main::RUN_AS_CLI);
        $this->expectOutputString('');
        $exception->printParentException();
    }

    public function testPrintParentExceptionCliWithParent(): void
    {
        $this->buildContainer('json', true);
        $exception = new ServerFailureException('Test', ResponseCode::HTTP_CODE_302, 5, new \Exception('Test'), Main::RUN_AS_CLI);
        $exception->printParentException();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString(
            str_replace("\r\n","\n",'Test
Thrown on line'),
            str_replace("\r\n","\n",$output)
        );
    }

    public function testPrintExceptionCli(): void
    {
        $this->buildContainer(setting: true);
        $exception = new ServerFailureException('Test', ResponseCode::HTTP_CODE_302, 5, null, Main::RUN_AS_CLI);
        $exception->printError();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString(
            str_replace("\r\n","\n",'Test
Thrown on line'),
            str_replace("\r\n","\n",$output)
        );
    }

    public function testPrintParentExceptionWebNoParent(): void
    {
        $this->buildContainer('json', true);
        $exception = new ServerFailureException('Test', ResponseCode::HTTP_CODE_302, 5, null, Main::RUN_AS_WEBAPP);
        $this->expectOutputString('');
        $exception->printParentException();
    }

    public function testPrintParentExceptionWebWithParent(): void
    {
        $this->buildContainer('json', true);
        $exception = new ServerFailureException('Test', ResponseCode::HTTP_CODE_302, 5, new \Exception('Test'), Main::RUN_AS_WEBAPP);
        $this->expectOutputRegex('/Test<br \/>Thrown on.*<table.*/');
        $exception->printParentException();
    }

    public function testGetResponseCode(): void
    {
        $this->buildContainer('json', true);
        $exception = new ServerFailureException('Test', ResponseCode::HTTP_CODE_302, 5, null, Main::RUN_AS_CLI);
        $this->assertEquals(ResponseCode::HTTP_CODE_302, $exception->getResponseCode());
    }

    protected function buildContainer(?string $format = null, ?bool $setting = null): void
    {
        $mockResponse = $this->createStub(ResponseInterface::class);
        $mockRequest = $this->createStub(RequestInterface::class);
        $mockConfig = $this->createStub(ConfigInterface::class);
        $mockConfig->method('getSetting')->willReturn($setting);
        $mockRequest->method('getArgumentString')->willReturn($format);

        /** @var ServiceContainer $serviceContainer */
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);

        $serviceContainer->add(ResponseInterface::class, $mockResponse);
        $serviceContainer->add(RequestInterface::class, $mockRequest);
        $serviceContainer->add(ConfigInterface::class, $mockConfig);
    }
}
