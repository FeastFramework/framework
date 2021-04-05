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

define('APPLICATION_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('CONTROLLERS_FOLDER', 'Controllers');
define('HANDLERS_FOLDER', 'Handlers');
define('PLUGINS_FOLDER', 'Plugins');
define('RUN_AS', 'cli');
require_once(APPLICATION_ROOT . '..' . DIRECTORY_SEPARATOR . 'Autoloader.php');
$autoLoader = new \Feast\Autoloader();
$autoLoader->register(['.php', '.php.txt']);
$autoLoader->addPathMapping('Feast', ['/..']);
$autoLoader->addPathMapping('Psr', ['/../Psr']);
// $config = \Feast\Config::getInstance(false,'test');
require_once(APPLICATION_ROOT . '..' . DIRECTORY_SEPARATOR . 'DependencyInjector.php');
require_once('Mocks/Curl.mock');
require_once('Mocks/ConfigClassFileFunctions.mock');
require_once('Mocks/ControllersFileFunctions.mock');
require_once('Mocks/DatabaseFunctions.mock');
require_once('Mocks/EmailClassFileFunctions.mock');
require_once('Mocks/FeastMocks.mock');
require_once('Mocks/FormClassFileFunctions.mock');
require_once('Mocks/FormValidatorsFileFunctions.mock');
require_once('Mocks/JobFileFunctions.mock');
require_once('Mocks/LoggerClassFileFunctions.mock');
require_once('Mocks/ProfilerFunctions.mock');
require_once('Mocks/RouterClassFileFunctions.mock');
require_once('Mocks/SessionFunctions.mock');
$container = di();
