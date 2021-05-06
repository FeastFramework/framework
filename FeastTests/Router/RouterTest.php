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

namespace Router;

use Feast\Exception\Error404Exception;
use Feast\Exception\NotFoundException;
use Feast\Exception\RouteException;
use Feast\Interfaces\RequestInterface;
use Feast\Main;
use Feast\Request;
use Feast\Router\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = '';
    }

    public function testDefaultRouteNonCli(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('');
        $this->assertEquals('IndexController', $router->getControllerClass());
        $this->assertEquals('index', $router->getControllerName());
        $this->assertEquals('indexAction', $router->getAction());
        $this->assertEquals('index', $router->getActionName());
        $this->assertEquals('Default', $router->getModuleName());
    }

    public function testKnownRouteNonCliGetNormal(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('/testing/service/test2/test3/');
        $this->assertEquals('TestingController', $router->getControllerClass());
        $this->assertEquals('testing', $router->getControllerName());
        $this->assertEquals('serviceAction', $router->getAction());
        $this->assertEquals('service', $router->getActionName());
        $this->assertEquals('serviceGet', $router->getActionMethodName());
    }

    public function testKnownRouteNonCliPostNormal(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('/testing/service/test2/test3/');
        $this->assertEquals('TestingController', $router->getControllerClass());
        $this->assertEquals('testing', $router->getControllerName());
        $this->assertEquals('serviceAction', $router->getAction());
        $this->assertEquals('service', $router->getActionName());
        $this->assertEquals('servicePost', $router->getActionMethodName());
    }

    public function testKnownRouteNonCliPutNormal(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('/testing/service/test2/test3/');
        $this->assertEquals('TestingController', $router->getControllerClass());
        $this->assertEquals('testing', $router->getControllerName());
        $this->assertEquals('serviceAction', $router->getAction());
        $this->assertEquals('service', $router->getActionName());
        $this->assertEquals('servicePut', $router->getActionMethodName());
    }

    public function testKnownRouteNonCliPatchNormal(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('/testing/service/test2/test3/');
        $this->assertEquals('TestingController', $router->getControllerClass());
        $this->assertEquals('testing', $router->getControllerName());
        $this->assertEquals('serviceAction', $router->getAction());
        $this->assertEquals('service', $router->getActionName());
        $this->assertEquals('servicePatch', $router->getActionMethodName());
    }

    public function testKnownRouteNonCliDeleteNormal(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('/testing/service/test2/test3/');
        $this->assertEquals('TestingController', $router->getControllerClass());
        $this->assertEquals('testing', $router->getControllerName());
        $this->assertEquals('serviceAction', $router->getAction());
        $this->assertEquals('service', $router->getActionName());
        $this->assertEquals('serviceDelete', $router->getActionMethodName());
    }

    public function testKnownRouteNonCliGetInternalNormal(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('feast/create/service');
        $this->assertEquals('CreateController', $router->getControllerClass());
        $this->assertEquals('create', $router->getControllerName());
        $this->assertEquals('serviceAction', $router->getAction());
        $this->assertEquals('service', $router->getActionName());
        $this->assertEquals('serviceGet', $router->getActionMethodName());
    }

    public function testKnownRouteNonCliGetModuleNormal(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('Test/feast-test/create-thing/service');
        $this->assertEquals('FeastTestController', $router->getControllerClass());
        $this->assertEquals('feastTest', $router->getControllerNameCamelCase());
        $this->assertEquals('feast-test', $router->getControllerName());
        $this->assertEquals('createThingAction', $router->getAction());
        $this->assertEquals('create-thing', $router->getActionName());
        $this->assertEquals('create-thing', $router->getActionNameDashes());
        $this->assertEquals('createThing', $router->getActionNameCamelCase());
        $this->assertEquals('createThingAction', $router->getActionMethodName());
    }

    public function testKnownRouteNonCliGetAlternate(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('testing/test');
        $this->assertEquals('TestingController', $router->getControllerClass());
        $this->assertEquals('testing', $router->getControllerName());
        $this->assertEquals('testAction', $router->getAction());
        $this->assertEquals('test', $router->getActionName());
        $this->assertEquals('testAction', $router->getActionMethodName());
    }

    public function testKnownRouteNonCliGetNoSubname(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('testing');
        $this->assertEquals('TestingController', $router->getControllerClass());
        $this->assertEquals('testing', $router->getControllerNameCamelCase());
        $this->assertEquals('testing', $router->getControllerName());
        $this->assertEquals('indexAction', $router->getAction());
        $this->assertEquals('index', $router->getActionNameCamelCase());
        $this->assertEquals('index', $router->getActionNameDashes());
        $this->assertEquals('index', $router->getActionName());
        $this->assertEquals('indexAction', $router->getActionMethodName());
    }

    public function testGetPathNonIndex(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('feasting/go');
        $this->assertEquals(
            'feasting/go/test2/test3?test4=test5',
            $router->getPath(null, null, ['test2' => 'test3'], ['test4' => 'test5'])
        );
    }

    public function testGetPathIndex(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('/');
        $this->assertEquals(
            '',
            $router->getPath()
        );
    }

    public function testGetReloop(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRouteForRequestUrl('feasting/go');
        $this->assertFalse($router->forwarded());
        $router->forward();
        $this->assertTrue($router->forwarded());
        $router->forward(false);
        $this->assertFalse($router->forwarded());
    }

    public function testCaching(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $this->assertFalse($router->isFromCache());
        $router->cache();
        /** @var Router $router */
        $router = unserialize(
            \Feast\Router\file_get_contents(
                APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'router.cache'
            )
        );
        $router->buildRoutes();
        $router->addRoute('test', 'test', 'test');
        $this->assertTrue($router instanceof Router);
        $this->assertTrue($router->isFromCache());
    }

    public function testRoutesWithName(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->addRoute('/im-a-teapot/:user/:pass', 'Teapot', 'ShortAndState', 'teapot', ['test' => 'testing']);
        $this->expectException(RouteException::class);
        $router->addRoute('/im-a-teapot/:user/:pass', 'Teapot', 'ShortAndState', 'teapot', ['test' => 'testing']);
    }

    public function testRoutesWithoutName(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->addRoute('/im-a-teapot/:user/:pass', 'Teapot', 'ShortAndStout', null, ['test' => 'testing']);
        $this->expectException(RouteException::class);
        $router->addRoute('/im-a-teapot/:user/:pass', 'Teapot', 'ShortAndStout', null, ['test' => 'testing']);
    }

    public function testRoutesWithNonExistentController(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->addRoute('/im-a-teapot/:user/:pass', 'Teapot', 'ShortAndStout', null, ['test' => 'testing']);
        $this->expectException(Error404Exception::class);
        $router->buildRouteForRequestUrl('im-a-teapot/1/2');
    }

    public function testRoutesWithOptionalParam(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->addRoute('im-a-teapot/:name/?:otherArgs', 'Testing', 'Service', 'im-a-teapot', ['name' => 'testing']);
        $router->buildRouteForRequestUrl('/im-a-teapot/test2');
        $this->assertEquals('Testing', $router->getControllerName());
        $this->assertEquals('im-a-teapot/testing', $router->getPath(route: 'im-a-teapot'));
        $this->assertEquals(
            'im-a-teapot/test2',
            $router->getPath(
                arguments: ['name' => 'test2', 'otherArgs' => []],
                route: 'im-a-teapot'
            )
        );
        $this->assertEquals('im-a-teapot', $router->getRouteName());
    }

    public function testRoutesWithStaticParam(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->addRoute(
            'im-a-teapot/:name/arg/?:otherArgs',
            'Testing',
            'Service',
            'im-a-teapot',
            ['name' => 'testing']
        );
        $router->buildRouteForRequestUrl('/im-a-teapot/test2/arg');
        $this->assertEquals('Testing', $router->getControllerName());
        $this->assertEquals('im-a-teapot/testing/arg', $router->getPath(route: 'im-a-teapot'));
        $this->assertEquals(
            'im-a-teapot/test2/arg/42',
            $router->getPath(
                arguments: ['name' => 'test2', 'otherArgs' => '42'],
                route: 'im-a-teapot'
            )
        );

        $this->assertEquals(
            'im-a-teapot/test2/arg/42/test',
            $router->getPath(
                arguments: ['name' => 'test2', 'otherArgs' => ['42', 'test']],
                route: 'im-a-teapot'
            )
        );
        $this->assertEquals('im-a-teapot', $router->getRouteName());
    }

    public function testRoutesWithNonExistentAction(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->addRoute('/im-a-teapot/:user/:pass', 'Testing', 'ShortAndStout', null, ['test' => 'testing']);
        $this->expectException(Error404Exception::class);
        $router->buildRouteForRequestUrl('im-a-teapot/2/1');
    }

    public function testRoutesWithNonExistentRoute(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $this->expectException(Error404Exception::class);
        $router->getPath(route: 'im-a-teapot');
    }

    public function testRoutesFunctional(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(RequestInterface::class, new Request());
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->addRoute('im-a-teapot/:name/:otherArgs', 'Testing', 'Service', 'im-a-teapot', ['name' => 'testing']);
        $router->buildRouteForRequestUrl('/im-a-teapot/test2/test/test3');
        $this->assertEquals('Testing', $router->getControllerName());
        $this->assertEquals('im-a-teapot/testing', $router->getPath(route: 'im-a-teapot'));
        $this->assertEquals(
            'im-a-teapot/test2/test1/test3',
            $router->getPath(
                arguments: ['name' => 'test2', 'otherArgs' => ['test1', 'test3']],
                route: 'im-a-teapot'
            )
        );
        $this->assertEquals('im-a-teapot', $router->getRouteName());
    }

    public function testCliRoute(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $request = new Request();
        $container->add(RequestInterface::class, $request);
        $router = new Router(Main::RUN_AS_CLI);
        $router->buildCliArguments('CLI/small/create/--color=orange/testing');
        $this->assertEquals(['color' => 'orange', 'name' => 'testing'], (array)$request->getAllArguments());
    }

    public function testCliRouteEmpty(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $request = new Request();
        $container->add(RequestInterface::class, $request);
        $router = new Router(Main::RUN_AS_CLI);
        $router->buildCliArguments('CLI');
        $this->assertEquals([], (array)$request->getAllArguments());
    }

    public function testCliRouteNonExistentController(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $request = new Request();
        $container->add(RequestInterface::class, $request);
        $router = new Router(Main::RUN_AS_CLI);
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Controller fake does not exist');
        $router->buildCliArguments('CLI/fake');
    }

    public function testCliRouteNonExistentAction(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $request = new Request();
        $container->add(RequestInterface::class, $request);
        $router = new Router(Main::RUN_AS_CLI);
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Action fakeAction does not exist');
        $router->buildCliArguments('CLI/small/fake');
    }

    public function testFeastRoute(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $request = new Request();
        $container->add(RequestInterface::class, $request);
        $router = new Router(Main::RUN_AS_CLI);
        $router->buildCliArguments('CLI/feast/create/action/--type=post/--fake=flag/new/place/potato');
        $this->assertEquals(
            ['type' => 'post', 'controller' => 'new', 'action' => 'place'],
            (array)$request->getAllArguments()
        );
    }

    public function testClearArguments(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $request = new Request();
        $container->add(RequestInterface::class, $request);
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->assignArguments(['test' => 'test2'], clearArguments: true);
        $this->assertEquals('test2', $request->getArgumentString('test'));
        $router->assignArguments([], clearArguments: true);
        $this->assertNull($request->getArgumentString('test'));
    }

    public function testRouteBuilt(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $request = new Request();
        $container->add(RequestInterface::class, $request);
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->buildRoutes();
        $router->buildRouteForRequestUrl('podcasts/listen/:podcast');
        $this->assertEquals('servicePost', $router->getActionMethodName());
    }

    public function testSetRunAs(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $request = new Request();
        $container->add(RequestInterface::class, $request);
        $router = new Router(Main::RUN_AS_WEBAPP);
        $router->setRunAs(Main::RUN_AS_CLI);
        $this->assertEquals(Main::RUN_AS_CLI, $router->getRunAs());
    }
}
