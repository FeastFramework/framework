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

##########################################################
# THIS VERSION OF THE BOOTSTRAP FILE ALLOWS PSALM TO RUN #
#             DO NOT MODIFY THIS FILE AT ALL             #
##########################################################
#

use Feast\Autoloader;
use Feast\Interfaces\RouterInterface;

if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    /** @psalm-suppress MissingFile */
    require_once(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
}
$routes = di(RouterInterface::class);
// Any custom routes below

$autoLoader = di(Autoloader::class);
// Any custom autoload mappings below
