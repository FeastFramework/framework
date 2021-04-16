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

use Feast\Exception\InvalidOptionException;
use Feast\Exception\NotFoundException;
use Feast\HttpRequest\Curl;
use Feast\HttpRequest\Simple;
use Feast\Interfaces\ConfigInterface;
use Feast\Service;
use Feast\ServiceContainer\ServiceContainer;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{

    public function testValidServiceClassCurl(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(ConfigInterface::class);
        $config->method('getSetting')->willReturnMap(
            [
                ['service.class', null, Curl::class]
            ]
        );
        $container->add(ConfigInterface::class, $config);
        $service = $this->getMockForAbstractClass(Service::class);

        $this->assertTrue($service->getHttpRequestObject() instanceof Curl);
    }

    public function testValidServiceClassSimple(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(ConfigInterface::class);
        $config->method('getSetting')->willReturnMap(
            [
                ['service.class', null, Simple::class]
            ]
        );
        $container->add(ConfigInterface::class, $config);
        $service = $this->getMockForAbstractClass(Service::class);

        $this->assertTrue($service->getHttpRequestObject() instanceof Simple);
    }

    public function testWithInvalidServiceClass(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(ConfigInterface::class);
        $config->method('getSetting')->willReturnMap(
            [
                ['service.class', null, stdClass::class]
            ]
        );
        $container->add(ConfigInterface::class, $config);
        $this->expectException(InvalidOptionException::class);

        $service = $this->getMockForAbstractClass(Service::class);
    }

    public function testWithNullServiceClass(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(ConfigInterface::class);

        $container->add(ConfigInterface::class, $config);
        $this->expectException(NotFoundException::class);

        $service = $this->getMockForAbstractClass(Service::class);
    }

}
