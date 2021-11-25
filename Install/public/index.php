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

/**
 * Main Web file.
 */

declare(strict_types=1);

use Feast\Autoloader;
use Feast\Interfaces\MainInterface;
use Feast\Main;
use Feast\ServiceContainer\ServiceContainer;

// Application start time
$startTime = microtime(true);

const APPLICATION_ROOT = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const CONTROLLERS_FOLDER = 'Controllers';
const HANDLERS_FOLDER = 'Handlers';
const PLUGINS_FOLDER = 'Plugins';

if (file_exists(APPLICATION_ROOT . 'maintenance.txt')) {
    http_response_code(503);

    include('maintenance-screen.html');
    exit;
}
if (file_exists(APPLICATION_ROOT . 'vendor/autoload.php')) {
    require_once(APPLICATION_ROOT . 'vendor/autoload.php');
}
// Initialize autoloader
if (file_exists(APPLICATION_ROOT . '/Feast/Autoloader.php')) {
    require_once(APPLICATION_ROOT . '/Feast/Autoloader.php');
}
$autoLoader = new Autoloader();
$autoLoader->register();

$autoLoader->addPathMapping('Psr', ['/Feast/Psr']);

const RUN_AS = Main::RUN_AS_WEBAPP;
require_once(APPLICATION_ROOT . 'container.php');
/** @var ServiceContainer $container */
$container->add(Autoloader::class, $autoLoader);

// Turn on all error reporting and turn OFF display errors by default
error_reporting(-1);
ini_set('display_errors', 'false');

$main = di(MainInterface::class);
$main->main();
