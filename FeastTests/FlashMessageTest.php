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

use Feast\FlashMessage;
use Feast\ServiceContainer\ServiceContainer;
use Feast\Session\Session;
use PHPUnit\Framework\TestCase;

class FlashMessageTest extends TestCase
{

    public function testGetMessageNoMessage(): void
    {
        /** @var ServiceContainer $serviceContainer */
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $session = $this->createStub(Session::class);
        $session->method('getNamespace')->willReturn(new stdClass());
        $serviceContainer->add(Session::class, $session);
        $this->assertEquals(null, FlashMessage::getMessage('test'));
    }

    public function testGetMessage(): void
    {
        $namespace = new stdClass();
        $namespace->test = 'Feast';
        /** @var ServiceContainer $serviceContainer */
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $session = $this->createStub(Session::class);
        $session->method('getNamespace')->willReturn($namespace);
        $serviceContainer->add(Session::class, $session);
        $this->assertEquals('Feast', FlashMessage::getMessage('test'));
    }

    public function testSetMessageAndGetMessage(): void
    {
        /** @var ServiceContainer $serviceContainer */
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $session = $this->createStub(Session::class);
        $session->method('getNamespace')->willReturn(new stdClass());
        $serviceContainer->add(Session::class, $session);
        $this->assertEquals(null, FlashMessage::getMessage('test'));
        FlashMessage::setMessage('test', 'Feast');
        $this->assertEquals('Feast', FlashMessage::getMessage('test'));
    }
}
