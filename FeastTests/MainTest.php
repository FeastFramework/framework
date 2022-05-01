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

use Feast\Config\Config;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\ErrorLoggerInterface;
use Feast\Interfaces\RequestInterface;
use Feast\Interfaces\ResponseInterface;
use Feast\Interfaces\RouterInterface;
use Feast\Logger\ErrorLogger;
use Feast\Main;
use Feast\Request;
use Feast\Response;
use Feast\View;
use PHPUnit\Framework\TestCase;

class MainTest extends TestCase
{

    public function testConstruct(): void
    {
        $main = $this->getMain(Main::RUN_AS_WEBAPP);
        $this->assertInstanceOf(Main::class,$main);
    }

    public function testMainWebApp404(): void
    {
        $main = $this->getMain(Main::RUN_AS_WEBAPP);
        $this->expectException(\Feast\Exception\Error404Exception::class);
        $main->main();
    }

    public function testMainCli404(): void
    {
        $main = $this->getMain(Main::RUN_AS_CLI);
        $this->expectException(\Feast\Exception\Error404Exception::class);
        $main->main();
    }

    public function testMainWebApp404Redirected(): void
    {
        $main = $this->getMain(Main::RUN_AS_WEBAPP, true);
        $main->main();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Location:/404ed', $output);
    }

    public function testMainWebAppThrottledRedirected(): void
    {
        $main = $this->getMain(Main::RUN_AS_WEBAPP, false, true);
        /** @var \PHPUnit\Framework\MockObject\Stub&RouterInterface $router */
        $router = di(RouterInterface::class);
        $router->method('getControllerClass')->willReturn('FeastTestController');
        $router->method('getControllerName')->willReturn('feast-test');
        $router->method('getControllerFullyQualifiedName')->willReturn(
            \Modules\Test\Controllers\FeastTestController::class
        );

        $router->method('getAction')->willReturn('serviceAction');
        $router->method('getActionName')->willReturn('service');
        $router->method('getActionMethodName')->willReturn('serviceGet');
        $router->method('getModuleName')->willReturn('Test');
        $main->main();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Location:/error/rate-limit', $output);
    }

    public function testMainWebAppNoAction(): void
    {
        $main = $this->getMain(Main::RUN_AS_WEBAPP);
        /** @var \PHPUnit\Framework\MockObject\Stub&RouterInterface $router */
        $router = di(RouterInterface::class);
        $router->method('getControllerClass')->willReturn('FeastTestController');
        $router->method('getControllerName')->willReturn('feast-test');
        $router->method('getControllerFullyQualifiedName')->willReturn(
            \Modules\Test\Controllers\FeastTestController::class
        );

        $router->method('getAction')->willReturn('serviceNoAction');
        $router->method('getActionName')->willReturn('serviceNo');
        $router->method('getActionMethodName')->willReturn('serviceNoGet');
        $router->method('getModuleName')->willReturn('Test');
        $this->expectException(\Feast\Exception\Error404Exception::class);
        $main->main();
    }

    public function testMainWebAppRedirect(): void
    {
        $main = $this->getMain(Main::RUN_AS_WEBAPP);
        /** @var \PHPUnit\Framework\MockObject\Stub&RouterInterface $router */
        $response = di(ResponseInterface::class);
        $response->method('getRedirectPath')->willReturn('/test');
        $main->main();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Location:/test', $output);
    }

    public function testMainWebAppInitFalse(): void
    {
        $main = $this->getMain(Main::RUN_AS_WEBAPP);
        /** @var \PHPUnit\Framework\MockObject\Stub&RouterInterface $router */
        $router = di(RouterInterface::class);
        $router->method('getControllerClass')->willReturn('FeastInitFailedController');
        $router->method('getControllerName')->willReturn('feast-test');
        $router->method('getControllerFullyQualifiedName')->willReturn(
            \Modules\Test\Controllers\FeastInitFailedController::class
        );

        $router->method('getAction')->willReturn('serviceAction');
        $router->method('getActionName')->willReturn('service');
        $router->method('getActionMethodName')->willReturn('serviceGet');
        $router->method('getModuleName')->willReturn('Test');
        $main->main();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Something went wrong! If you are the administrator, check the error logs for more info.<br><br>', $output);
    }

    public function testMainWebAppNormal(): void
    {
        $main = $this->getMain(Main::RUN_AS_WEBAPP);
        /** @var \PHPUnit\Framework\MockObject\Stub&RouterInterface $router */
        $router = di(RouterInterface::class);
        $router->method('getControllerClass')->willReturn('FeastTestController');
        $router->method('getControllerName')->willReturn('feast-test');
        $router->method('getControllerFullyQualifiedName')->willReturn(
            \Modules\Test\Controllers\FeastTestController::class
        );

        $router->method('getAction')->willReturn('serviceAction');
        $router->method('getActionName')->willReturn('service');
        $router->method('getActionMethodName')->willReturn('serviceGet');
        $router->method('getModuleName')->willReturn('Test');
        $main->main();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Success!', $output);
    }

    public function testMainCliNormal(): void
    {
        $main = $this->getMain(Main::RUN_AS_CLI);
        /** @var \PHPUnit\Framework\MockObject\Stub&RouterInterface $router */
        $router = di(RouterInterface::class);
        $router->method('getControllerClass')->willReturn('FeastTestController');
        $router->method('getControllerName')->willReturn('feast-test');
        $router->method('getControllerFullyQualifiedName')->willReturn(
            \Modules\Test\Controllers\FeastTestController::class
        );

        $router->method('getAction')->willReturn('serviceAction');
        $router->method('getActionName')->willReturn('service');
        $router->method('getActionMethodName')->willReturn('serviceGet');
        $router->method('getModuleName')->willReturn('Test');
        $main->main();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Success!', $output);
    }
    

    public function testMainWebAppJson(): void
    {
        $main = $this->getMain(Main::RUN_AS_WEBAPP);
        $_SERVER['REQUEST_URI'] = '/test';
        /** @var \PHPUnit\Framework\MockObject\Stub&RouterInterface $router */
        $router = di(RouterInterface::class);
        $router->method('getControllerClass')->willReturn('FeastTestController');
        $router->method('getControllerName')->willReturn('feast-test');
        $router->method('getControllerFullyQualifiedName')->willReturn(
            \Modules\Test\Controllers\FeastTestController::class
        );

        $router->method('getAction')->willReturn('serviceAction');
        $router->method('getActionName')->willReturn('service');
        $router->method('getActionMethodName')->willReturn('serviceGet');
        $router->method('getModuleName')->willReturn('Test');

        /** @var \PHPUnit\Framework\MockObject\Stub&ResponseInterface $response */
        $response = di(ResponseInterface::class);
        $response->method('isJson')->willReturn(true);
        $main->main();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Success!', $output);
        unset($_SERVER['REQUEST_URI']);
    }

    public function testMainWebAppException(): void
    {
        $main = $this->getMain(Main::RUN_AS_WEBAPP);
        /** @var \PHPUnit\Framework\MockObject\Stub&RouterInterface $router */
        $router = di(RouterInterface::class);
        $router->method('getControllerClass')->willReturn('FeastTestController');
        $router->method('getControllerName')->willReturn('feast-test');
        $router->method('getControllerFullyQualifiedName')->willReturn(
            \Modules\Test\Controllers\FeastTestController::class
        );

        $router->method('getAction')->willReturn('exceptionAction');
        $router->method('getActionName')->willReturn('exception');
        $router->method('getActionMethodName')->willReturn('exceptionGet');
        $router->method('getModuleName')->willReturn('Test');
        $main->main();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Something went wrong! If you are the administrator, check the error logs for more info.<br><br>', $output);
    }

    public function testMainWebAppNormalWithModel(): void
    {
        $main = $this->getMain(Main::RUN_AS_WEBAPP);
        /** @var \PHPUnit\Framework\MockObject\Stub&RouterInterface $router */
        $router = di(RouterInterface::class);
        $router->method('getControllerClass')->willReturn('FeastTestController');
        $router->method('getControllerName')->willReturn('feast-test');
        $router->method('getControllerFullyQualifiedName')->willReturn(
            \Modules\Test\Controllers\FeastTestController::class
        );

        $router->method('getAction')->willReturn('modelAction');
        $router->method('getActionName')->willReturn('model');
        $router->method('getActionMethodName')->willReturn('modelGet');
        $router->method('getModuleName')->willReturn('Test');
        $main->main();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Model Success!', $output);
    }

    protected function getMain(string $runAs, bool $with404 = false, bool $withThrottle = false): Main
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        if ($with404) {
            $config->method('getSetting')->willReturnMap(
                [
                    [
                        'buildroutes',
                        null,
                        true
                    ],
                    [
                        'error.http404.url',
                        'error/fourohfour',
                        '404ed'
                    ],
                    [
                        'plugin',
                        null,
                        [
                            \Plugins\TestPlugin::class,
                            \Plugins\TestPlugin::class
                        ]
                    ]
                ]
            );
        } elseif ($withThrottle) {
            $config->method('getSetting')->willReturnMap(
                [
                    [
                        'buildroutes',
                        null,
                        true
                    ],
                    [
                        'error.throttle.url',
                        'error/rate-limit',
                        'error/rate-limit'
                    ],
                    [
                        'plugin',
                        null,
                        [
                            \Plugins\ThrottleExceptionPlugin::class,
                            \Plugins\ThrottleExceptionPlugin::class
                        ]
                    ]
                ]
            );
        } else {
            $config->method('getSetting')->willReturnMap(
                [
                    [
                        'buildroutes',
                        null,
                        true
                    ]
                ]
            );
        }

        $container->add(ConfigInterface::class, $config);
        $container->add(ErrorLoggerInterface::class, $this->createStub(ErrorLogger::class));
        $container->add(RouterInterface::class, $this->createStub(Feast\Router\Router::class));
        $request = $this->createStub(Request::class);
        $request->method('getArgumentString')->willReturnMap(
            [
                [
                    'model',
                    null,
                    'MAINTEST'
                ]
            ]
        );
        $request->method('getArgumentBool')->willReturnMap(
            [
                [
                    'boolTest',
                    null,
                    true
                ]
            ]
        );
        $request->method('getArgumentFloat')->willReturnMap(
            [
                [
                    'floatTest',
                    null,
                    1.2
                ]
            ]
        );
        $request->method('getArgumentInt')->willReturnMap(
            [
                [
                    'intTest',
                    null,
                    1
                ]
            ]
        );
        $request->method('getArgumentDate')->willReturnMap(
            [
                [
                    'dateTest',
                    '2020-01-01 00:00:00'
                ]
            ]
        );
        $request->method('getArgumentArray')->willReturnMap(
            [
                [
                    'arrayTest',
                    null,
                    'array',
                    ['test']
                ],
                [
                    'extra',
                    [null],
                    'string',
                    ['test2', 'test23']
                ]
            ]
        );
        $container->add(RequestInterface::class, $request);
        $container->add(ResponseInterface::class, $this->createStub(ResponseInterface::class));
        $container->add(
            \Feast\Interfaces\DatabaseFactoryInterface::class,
            $this->createStub(\Feast\Interfaces\DatabaseFactoryInterface::class)
        );
        $view = $this->createStub(View::class);
        $view->test = 'test';
        $container->add(View::class, $view);
        return new Main($container, $runAs);
    }
}
