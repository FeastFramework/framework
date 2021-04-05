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

namespace ServiceContainer;

use Feast\ServiceContainer\ContainerException;
use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainer;
use Mocks\InjectedMock;
use PHPUnit\Framework\TestCase;

class ServiceContainerTest extends TestCase
{

    public function testGet(): void
    {
        /** @var ServiceContainer $serviceContainer */
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $item = $serviceContainer->get(ServiceContainer::class);
        $this->assertTrue($item instanceof ServiceContainer);
    }

    public function testGetInvalid(): void
    {
        /** @var ServiceContainer $serviceContainer */
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $this->expectException(NotFoundException::class);
        $item = $serviceContainer->get(\PDO::class);
    }

    public function testAddInvalid(): void
    {
        /** @var ServiceContainer $serviceContainer */
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $serviceContainer->add(\stdClass::class, new \stdClass());
        $this->expectException(ContainerException::class);
        $serviceContainer->add(\stdClass::class, new \stdClass());
    }

    public function testReplace(): void
    {
        $object = new \stdClass();
        $object->name = 'test';
        /** @var ServiceContainer $serviceContainer */
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $serviceContainer->add(\stdClass::class, new \stdClass());
        $serviceContainer->replace(\stdClass::class, $object);

        $pulledObject = $serviceContainer->get(\stdClass::class);
        $this->assertEquals($object->name, $pulledObject->name);
    }

    public function testInjectedCheck(): void
    {
        /** @var ServiceContainer $serviceContainer */
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $serviceContainer->add(InjectedMock::class, new InjectedMock());
        $this->expectException(ContainerException::class);
        new InjectedMock();
    }
}
