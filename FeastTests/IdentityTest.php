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

use Feast\Interfaces\ConfigInterface;
use Feast\ServiceContainer\ServiceContainer;
use Feast\Session\Identity;
use Feast\Session\Session;
use Mocks\MockUser;
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
{

    public function testGetUser(): void
    {
        di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $session = $this->createStub(Session::class);
        $namespace = new stdClass();
        $testUser = new MockUser();
        $testUser->user = 'testUser';
        $namespace->identity = $testUser;

        $session->method('getNamespace')->willReturn($namespace);
        $config = $this->createStub(ConfigInterface::class);
        $config->method('getSetting')->willReturn('test');

        $identity = new Identity($session);
        $user = $identity->getUser();
        $this->assertEquals('testUser', $user->user);
    }

    public function testSaveUser(): void
    {
        di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $session = $this->createStub(Session::class);
        $namespace = new stdClass();
        $session->Feast_Login = $namespace;

        $config = $this->createStub(ConfigInterface::class);
        $config->method('getSetting')->willReturn('test');

        $testUser = new MockUser();
        $testUser->user = 'testUser';
        $namespace->identity = $testUser;
        $identity = new Identity($session);
        $user = $identity->getUser();
        $this->assertEquals(null, $user);

        $identity->saveUser($testUser);
        $user = $identity->getUser();
        $this->assertEquals('testUser', $user->user);
    }

    public function testDestroyUser(): void
    {
        di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $session = $this->createStub(Session::class);
        $namespace = new stdClass();
        $session->Feast_Login = $namespace;

        $config = $this->createStub(ConfigInterface::class);
        $config->method('getSetting')->willReturn('test');

        $testUser = new MockUser();
        $testUser->user = 'testUser';

        $identity = new Identity($session);
        $identity->saveUser($testUser);

        $user = $identity->getUser();
        $this->assertEquals('testUser', $user->user);

        $identity->destroyUser();
        $user = $identity->getUser();
        $this->assertEquals(null, $user);
    }
}
