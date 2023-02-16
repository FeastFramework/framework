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

namespace Controllers;

use Feast\CliArguments;
use Feast\Config\Config;
use Feast\Controllers\CacheController;
use Feast\Interfaces\DatabaseDetailsInterface;
use Feast\Interfaces\RouterInterface;
use PHPUnit\Framework\TestCase;

class CacheControllerTest extends TestCase
{

    public function testConfigClearGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CacheController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:cache:config-clear'])
        );
        $controller->configClearGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Config cache cleared!', trim($output));
    }

    public function testRouterClearGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CacheController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:cache:router-clear'])
        );
        $controller->routerClearGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Router cache cleared!', trim($output));
    }

    public function testDbClearGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CacheController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:cache:config-clear'])
        );
        $controller->dbinfoClearGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Database info cache cleared!', trim($output));
    }

    public function testClearAllGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CacheController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:cache:clear-all'])
        );
        $controller->clearAllGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Config cache cleared!' . PHP_EOL . 'Router cache cleared!' . PHP_EOL . 'Database info cache cleared!', trim($output));
    }

    public function testRouterGenerateGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CacheController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:cache:router-generate'])
        );
        $controller->routerGenerateGet($this->createStub(RouterInterface::class));
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Router cached!', trim($output));
    }

    public function testConfigGenerateGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CacheController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:cache:config-generate'])
        );
        $controller->configGenerateGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Config cached!', trim($output));
    }

    public function testDatabaseInfoGenerateGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CacheController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:cache:router-generate'])
        );
        $controller->dbinfoGenerateGet($this->createStub(DatabaseDetailsInterface::class));
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Database info cached!', trim($output));
    }

    public function testCacheAllGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CacheController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:cache:cache-all'])
        );
        $controller->cacheAllGet($this->createStub(DatabaseDetailsInterface::class),$this->createStub(RouterInterface::class));
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Config cached!' . PHP_EOL . 'Router cached!' . PHP_EOL . 'Database info cached!', trim($output));
    }
}
