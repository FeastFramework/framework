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
use Feast\Controllers\MaintenanceController;
use Feast\Enums\DocType;
use Feast\Interfaces\RouterInterface;
use Feast\ServiceContainer\ServiceContainer;
use Feast\View;
use PHPUnit\Framework\TestCase;

class MaintenanceControllerTest extends TestCase
{

    public function setUp(): void
    {
        \Feast\Controllers\unlink(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Error' . DIRECTORY_SEPARATOR . 'maintenance-screen.phtml');
    }

    public function testStartGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls( false);
        /** @var ServiceContainer $di */
        $di = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $view = $this->createStub(View::class);
        $di->add(View::class, $view);
            
        $controller = new MaintenanceController(
            $di,
            $config,
            new CliArguments(['famine', 'feast:maintenance:start'])
        );
        $controller->startGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('maintenance-screen.phtmlThe website is undergoing maintenance.maintenance.txt1maintenance-screen.htmlMaintenance mode enabled!
Views/Error/maintenance-screen.phtml has been generated to the public folder as maintenance-screen.html', trim($output));
    }

    public function testStopGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls( false);
        $di = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $view = $this->createStub(View::class);
        $di->add(View::class, $view);
        $controller = new MaintenanceController(
            $di,
            $config,
            new CliArguments(['famine', 'feast:maintenance:stop'])
        );
        $controller->startGet();
        $controller->stopGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Maintenance mode disabled!', trim($output));
    }
}
