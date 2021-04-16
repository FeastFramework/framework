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

use Controllers\EmptyCliController;
use Controllers\EmptyController;
use Feast\HttpController;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{

    public function testAlwaysJson(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $view = $this->createStub(\Feast\View::class);
        $controller = new EmptyController($container, $view);

        $this->assertFalse($controller->alwaysJson('test'));
    }

    public function testCliAlwaysJson(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $controller = new EmptyCliController($container, $this->createStub(\Feast\Interfaces\ConfigInterface::class));
        $this->assertFalse($controller->alwaysJson('test'));
    }

    public function testAllowJsonForAction(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);

        $mockRouter = $this->createStub(\Feast\Interfaces\RouterInterface::class);
        $mockRouter->method('getActionName')->willReturn('test');
        $mockRouter->method('getActionNameCamelCase')->willReturn('test');
        $container->add(\Feast\Interfaces\RouterInterface::class, $mockRouter);

        $view = $this->createStub(\Feast\View::class);
        $controller = new EmptyController($container, $view);
        $controller->allowJsonForAction('test');

        $this->assertTrue($controller->jsonAllowed());
    }

    public function testAllowJsonForDifferentAction(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);

        $mockRouter = $this->createStub(\Feast\Interfaces\RouterInterface::class);
        $mockRouter->method('getActionName')->willReturn('test');
        $mockRouter->method('getActionNameCamelCase')->willReturn('test');
        $container->add(\Feast\Interfaces\RouterInterface::class, $mockRouter);

        $view = $this->createStub(\Feast\View::class);
        $controller = new EmptyController($container, $view);
        $controller->allowJsonForAction('testing');

        $this->assertFalse($controller->jsonAllowed());
    }

    public function testForward(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);

        $mockRouter = $this->createStub(\Feast\Interfaces\RouterInterface::class);
        $container->add(\Feast\Interfaces\RouterInterface::class, $mockRouter);

        $view = $this->createStub(\Feast\View::class);
        $controller = new EmptyController($container, $view);
        $controller->forward();
        // If we get this far it didn't fail. The Router tests will test the individual logic pieces.
        $this->assertTrue($controller instanceof HttpController);
    }

    public function testExternalRedirect(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);

        $mockResponse = $this->createStub(\Feast\Interfaces\ResponseInterface::class);
        $container->add(\Feast\Interfaces\ResponseInterface::class, $mockResponse);

        $view = $this->createStub(\Feast\View::class);
        $controller = new EmptyController($container, $view);
        $controller->externalRedirect('http://www.google.com');
        // If we get this far it didn't fail. The Router tests will test the individual logic pieces.
        $this->assertTrue($controller instanceof HttpController);
    }

    public function testInit(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $view = $this->createStub(\Feast\View::class);
        $controller = new EmptyController($container, $view);
        $this->assertTrue($controller->init());
    }

    public function testCliInit(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $controller = new EmptyCliController($container, $this->createStub(\Feast\Interfaces\ConfigInterface::class));
        $this->assertTrue($controller->init());
    }

    public function testRedirect(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);

        $mockRouter = $this->createStub(\Feast\Interfaces\RouterInterface::class);
        $container->add(\Feast\Interfaces\RouterInterface::class, $mockRouter);

        $mockResponse = $this->createStub(\Feast\Interfaces\ResponseInterface::class);
        $container->add(\Feast\Interfaces\ResponseInterface::class, $mockResponse);
        $view = $this->createStub(\Feast\View::class);
        $controller = new EmptyController($container, $view);
        $controller->redirect();
        // If we get this far it didn't fail. The Router tests will test the individual logic pieces.
        $this->assertTrue($controller instanceof HttpController);
    }
}
