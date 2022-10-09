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

use Feast\Session\Session;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{

    public function testGetNamespace(): void
    {
        \Feast\Session\MockSession::reset();
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(
            \Feast\Interfaces\RouterInterface::class,
            $this->createStub(\Feast\Interfaces\RouterInterface::class)
        );
        $_SESSION['Feast'] = new stdClass();
        $_SESSION['Feast']->ipAddress = '127.0.0.1';
        $config = $this->createStub(\Feast\Interfaces\ConfigInterface::class);
        $config->method('getSetting')->willReturnMap(
            [
                [
                    'session.strictIp',
                    false,
                    true
                ]
            ]
        );
        $session = new Session($config);
        $this->assertInstanceOf(stdClass::class, $session->getNamespace('test'));
    }

    public function testGetNamespaceDisabled(): void
    {
        \Feast\Session\MockSession::reset();
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(
            \Feast\Interfaces\RouterInterface::class,
            $this->createStub(\Feast\Interfaces\RouterInterface::class)
        );
        $_SESSION['Feast'] = new stdClass();
        $_SESSION['Feast']->ipAddress = '127.0.0.1';
        $config = $this->createStub(\Feast\Interfaces\ConfigInterface::class);
        $config->method('getSetting')->willReturnMap(
            [
                [
                    'session.enabled',
                    true,
                    false
                ]
            ]
        );
        $session = new Session($config);
        $this->expectException(\Feast\Exception\SessionNotStartedException::class);
        $session->getNamespace('test');
    }

    public function testConstructGoodIp(): void
    {
        \Feast\Session\MockSession::reset();
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(
            \Feast\Interfaces\RouterInterface::class,
            $this->createStub(\Feast\Interfaces\RouterInterface::class)
        );
        $_SESSION['Feast'] = new stdClass();
        $_SESSION['Feast']->ipAddress = '127.0.0.1';
        $config = $this->createStub(\Feast\Interfaces\ConfigInterface::class);
        $config->method('getSetting')->willReturnMap(
            [
                [
                    'session.strictIp',
                    false,
                    true
                ]
            ]
        );
        $session = new Session($config);

        $this->assertInstanceOf(Session::class, $session);
    }

    public function testConstructBadIp(): void
    {
        \Feast\Session\MockSession::reset();
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(
            \Feast\Interfaces\ResponseInterface::class,
            $this->createStub(\Feast\Interfaces\ResponseInterface::class)
        );
        $_SESSION['Feast'] = new stdClass();
        $_SESSION['Feast']->ipAddress = '127.0.0.2';
        $config = $this->createStub(\Feast\Interfaces\ConfigInterface::class);
        $config->method('getSetting')->willReturnMap(
            [
                [
                    'session.strictIp',
                    false,
                    true
                ]
            ]
        );
        $session = new Session($config);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertTrue(!isset($_SESSION['Feast']));
    }

    public function testDestroyNamespace(): void
    {
        \Feast\Session\MockSession::reset();
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(
            \Feast\Interfaces\RouterInterface::class,
            $this->createStub(\Feast\Interfaces\RouterInterface::class)
        );
        $_SESSION['Feast'] = new stdClass();
        $_SESSION['Feast']->ipAddress = '127.0.0.1';
        $config = $this->createStub(\Feast\Interfaces\ConfigInterface::class);
        $config->method('getSetting')->willReturnMap(
            [
                [
                    'session.strictIp',
                    false,
                    true
                ]
            ]
        );
        $session = new Session($config);
        $session->getNamespace('test');
        $session->destroyNamespace('test');
        $this->assertTrue(!isset($_SESSION['test']));
    }

    public function testDestroyNamespaceDisabled(): void
    {
        \Feast\Session\MockSession::reset();
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(
            \Feast\Interfaces\RouterInterface::class,
            $this->createStub(\Feast\Interfaces\RouterInterface::class)
        );
        $_SESSION['Feast'] = new stdClass();
        $_SESSION['Feast']->ipAddress = '127.0.0.1';
        $config = $this->createStub(\Feast\Interfaces\ConfigInterface::class);
        $config->method('getSetting')->willReturnMap(
            [
                [
                    'session.enabled',
                    true,
                    false
                ]
            ]
        );
        $session = new Session($config);
        $this->expectException(\Feast\Exception\SessionNotStartedException::class);
        $session->destroyNamespace('test');
    }
    
    public function testIsEnabled(): void
    {
        \Feast\Session\MockSession::reset();
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(
            \Feast\Interfaces\RouterInterface::class,
            $this->createStub(\Feast\Interfaces\RouterInterface::class)
        );
        $config = $this->createStub(\Feast\Interfaces\ConfigInterface::class);
        $config->method('getSetting')->willReturnMap(
            [
                [
                    'session.enabled',
                    true,
                    false
                ]
            ]
        );
        $session = new Session($config);
        $this->assertFalse($session->isEnabled());
    }
}
