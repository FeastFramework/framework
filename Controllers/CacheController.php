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
use Feast\CliController;
use Feast\Config\Config;
use Feast\Interfaces\DatabaseDetailsInterface;
use Feast\Interfaces\RouterInterface;

class CacheController extends CliController
{
    protected const CONFIG_FILE_NAME = 'config.cache';
    protected const ROUTER_FILE_NAME = 'router.cache';
    protected const DATABASE_FILE_NAME = 'database.cache';

    #[Action(description: 'Clear config cache file.')]
    public function configClearGet(): void
    {
        $cachePath = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        if (file_exists($cachePath . self::CONFIG_FILE_NAME)) {
            unlink($cachePath . self::CONFIG_FILE_NAME);
        }
        $this->terminal->command('Config cache cleared!');
    }

    #[Action(description: 'Clear config cache file (if any) and regenerate.')]
    public function configGenerateGet(): void
    {
        $config = new Config(false);
        $config->cacheConfig();
        $this->terminal->command('Config cached!');
    }

    #[Action(description: 'Clear router cache file.')]
    public function routerClearGet(): void
    {
        $cachePath = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        if (file_exists($cachePath . self::ROUTER_FILE_NAME)) {
            unlink($cachePath . self::ROUTER_FILE_NAME);
        }
        $this->terminal->command('Router cache cleared!');
    }

    #[Action(description: 'Clear router cache file (if any) and regenerate.')]
    public function routerGenerateGet(
        RouterInterface $router
    ): void {
        $router->cache();
        $this->terminal->command('Router cached!');
    }

    #[Action(description: 'Clear database info cache file.')]
    public function dbinfoClearGet(): void
    {
        $cachePath = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        if (file_exists($cachePath . self::DATABASE_FILE_NAME)) {
            unlink($cachePath . self::DATABASE_FILE_NAME);
        }
        $this->terminal->command('Database info cache cleared!');
    }

    #[Action(description: 'Clear database info cache file (if any) and regenerate.')]
    public function dbinfoGenerateGet(
        DatabaseDetailsInterface $databaseDetails
    ): void {
        $databaseDetails->cache();
        $this->terminal->command('Database info cached!');
    }       


}
