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

namespace Feast\Controllers;

use Feast\Attributes\Action;
use Feast\Attributes\Param;
use Feast\CliController;
use Feast\Enums\ParamType;

class ServeController extends CliController
{

    #[Action(usage: '--hostname={hostname} --port={port-number} --workers={worker-count}', description: 'Serve the site via PHP\'s built in web server')]
    #[Param(type: 'string', name: 'hostname', description: 'Hostname to run on [Defaults to localhost]', paramType: ParamType::FLAG)]
    #[Param(type: 'int', name: 'port', description: 'Port to run on [Defaults to 8000]', paramType: ParamType::FLAG)]
    #[Param(type: 'int', name: 'workers', description: 'Number of workers [Defaults to 1]', paramType: ParamType::FLAG)]
    public function serveGet(
        string $hostname = 'localhost',
        int $port = 8000,
        int $workers = 1
    ): void {
        if ($workers > 1) {
            putenv('PHP_CLI_SERVER_WORKERS=' . (string)$workers);
        }
        passthru(
            'php -S ' . $hostname . ':' . (string)$port . ' ' . APPLICATION_ROOT . 'bin' . DIRECTORY_SEPARATOR . 'router.php'
        );
    }

}
