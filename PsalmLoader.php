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
use Feast\Database\DatabaseFactory;
use Feast\Logger\ErrorLogger;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\DatabaseFactoryInterface;
use Feast\Interfaces\ErrorLoggerInterface;
use Feast\Interfaces\LoggerInterface;
use Feast\Interfaces\ProfilerInterface;
use Feast\Interfaces\RequestInterface;
use Feast\Interfaces\ResponseInterface;
use Feast\Interfaces\RouterInterface;
use Feast\Logger\Logger;
use Feast\Response;
use Feast\Profiler\Profiler;
use Feast\Request;
use Feast\Router\Router;
use Feast\View;

define('APPLICATION_ROOT', __DIR__ . '/');
define('CONTROLLERS_FOLDER', 'Controllers');
define('PLUGINS_FOLDER', 'Plugins');

// Initialize autoloader
require_once(APPLICATION_ROOT . 'Autoloader.php');
$autoLoader = new \Feast\Autoloader();
$autoLoader->register(['.php', '.php.txt']);
$autoLoader->addPathMapping('Feast', ['.']);
$autoLoader->addPathMapping('Psr', ['./Psr']);
$autoLoader->addPathMapping('Mapper', ['./Install/Mapper']);
$autoLoader->addPathMapping('Model', ['./Install/Model']);

if (file_exists('vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    require_once('vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
}
require_once(APPLICATION_ROOT . 'DependencyInjector.php');
$container = di();
$config = new Config(overriddenEnvironment: 'development');
$logger = new Logger($config, \Feast\Main::RUN_AS_CLI);

$container->add(ConfigInterface::class, $config);
$container->add(DatabaseFactoryInterface::class, new DatabaseFactory($config));
$container->add(RouterInterface::class, new Router());
$router = di(RouterInterface::class);

$container->add(View::class, new View($config, $router));
$container->add(RequestInterface::class, new Request());
$container->add(ProfilerInterface::class, new Profiler(microtime(true)));
$container->add(LoggerInterface::class, $logger);
$container->add(ErrorLoggerInterface::class, new ErrorLogger($logger));
$container->add(ResponseInterface::class, new Response());

define('RUN_AS', \Feast\Main::RUN_AS_CLI);

