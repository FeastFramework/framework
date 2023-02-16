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
use Feast\Controllers\ServeController;
use PHPUnit\Framework\TestCase;

class ServeControllerTest extends TestCase
{

    public function testServeGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new ServeController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:serve'])
        );
        $controller->serveGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('php -S localhost:8000 ', trim($output));
        $this->assertStringEndsWith('bin' . DIRECTORY_SEPARATOR . 'router.php', trim($output));
    }

    public function testServeGetWithParams(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new ServeController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:serve'])
        );
        $controller->serveGet('127.0.0.1', 5000, 7);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('PHP_CLI_SERVER_WORKERS=7php -S 127.0.0.1:5000 ', trim($output));
        $this->assertStringEndsWith('bin' . DIRECTORY_SEPARATOR . 'router.php', trim($output));
    }

}
