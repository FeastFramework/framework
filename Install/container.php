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

use Feast\CliArguments;
use Feast\Config\Config;
use Feast\Database\DatabaseDetails;
use Feast\Database\DatabaseFactory;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\DatabaseDetailsInterface;
use Feast\Interfaces\DatabaseFactoryInterface;
use Feast\Interfaces\ErrorLoggerInterface;
use Feast\Interfaces\LoggerInterface;
use Feast\Interfaces\MainInterface;
use Feast\Interfaces\ProfilerInterface;
use Feast\Interfaces\RequestInterface;
use Feast\Interfaces\ResponseInterface;
use Feast\Interfaces\RouterInterface;
use Feast\Logger\ErrorLogger;
use Feast\Logger\Logger;
use Feast\Main;
use Feast\Profiler\Profiler;
use Feast\Request;
use Feast\Response;
use Feast\Router\Router;
use Feast\ServiceContainer\ServiceContainer;
use Feast\Session\Identity;
use Feast\Session\Session;
use Feast\View;

if (!function_exists('di') && file_exists(
        APPLICATION_ROOT . 'Feast' . DIRECTORY_SEPARATOR . 'DependencyInjector.php'
    )) {
    require_once(APPLICATION_ROOT . 'Feast' . DIRECTORY_SEPARATOR . 'DependencyInjector.php');
}
###############################################
# THE PROFILER IS LOADED AS EARLY AS POSSIBLE #
# IF YOU MOVE THIS, TIMING WILL BE INACCURATE #
###############################################
$profiler = new Profiler(microtime(true));
/** @var ServiceContainer $container */
$container = di();

############################################
# CONFIG IS LOADED FROM CACHE IF AVAILABLE #
############################################
$configCache = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'config.cache';
if (file_exists($configCache)) {
    /** @var ConfigInterface|false $config */
    $config = unserialize(file_get_contents($configCache));
    if (!$config instanceof ConfigInterface) {
        $config = new Config();
    }
} else {
    $config = new Config();
}
/** @var string $runAs
 * @noinspection PhpRedundantVariableDocTypeInspection
 */
$runAs = RUN_AS;
$logger = new Logger($config, $runAs);

############################################
# ROUTER IS LOADED FROM CACHE IF AVAILABLE #
############################################
$routerCache = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'router.cache';
if (file_exists($routerCache)) {
    /** @var RouterInterface|false $router */
    $router = unserialize(file_get_contents($routerCache));
    if (!$router instanceof RouterInterface) {
        $logger->error('Router cache file is not a valid router object. Discarding.');
        /** @psalm-suppress MixedArgument */
        $router = new Router($runAs);
    }
} else {
    /** @psalm-suppress MixedArgument */
    $router = new Router($runAs);
}
$container->add(RouterInterface::class, $router);

#########################################################
#       ADD YOUR CUSTOM INJECTABLE CLASSES BELOW        #
#           THESE CLASSES MUST IMPLEMENT THE            #
# Feast\ServiceContainer\ServiceContainerItem interface #
#########################################################

#################################################################################
# DO NOT EDIT BELOW THIS LINE UNLESS YOU ARE CHANGING FRAMEWORK IMPLEMENTATIONS #
#################################################################################
$container->add(ProfilerInterface::class, $profiler);
$container->add(ConfigInterface::class, $config);
$databaseFactory = new DatabaseFactory($config);
$container->add(DatabaseFactoryInterface::class, $databaseFactory);

###################################################
# DATABASE INFO IS LOADED FROM CACHE IF AVAILABLE #
###################################################
$databaseCache = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'database.cache';
if (file_exists($databaseCache)) {
    /** @var DatabaseDetailsInterface|false $databaseDetails */
    $databaseDetails = unserialize(file_get_contents($databaseCache));
    if (!$databaseDetails instanceof DatabaseDetailsInterface) {
        $logger->error('Database info cache file is not a valid database details object. Discarding.');
        /** @psalm-suppress MixedArgument */
        $databaseDetails = new DatabaseDetails($databaseFactory);
    } else {
        $databaseDetails->setDatabaseFactory($databaseFactory);
    }
} else {
    /** @psalm-suppress MixedArgument */
    $databaseDetails = new DatabaseDetails($databaseFactory);
}
$container->add(DatabaseDetailsInterface::class, $databaseDetails);

$container->add(View::class, new View($config, $router));
$container->add(RequestInterface::class, new Request());
$container->add(LoggerInterface::class, $logger);
$container->add(ErrorLoggerInterface::class, new ErrorLogger($logger));
$container->add(ResponseInterface::class, new Response());
$container->add(MainInterface::class, new Main($container, $runAs));
if (RUN_AS === Main::RUN_AS_WEBAPP) {
    $session = new Session($config);
    $container->add(Session::class, $session);
    $container->add(Identity::class, new Identity($config, $session));
} else {
    $container->add(CliArguments::class, new CliArguments($argv));
}

unset($config);
unset($session);
